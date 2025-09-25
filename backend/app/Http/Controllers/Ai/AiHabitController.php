<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AiHabitController extends Controller
{
    private function callAI($prompt)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => 'AIzaSyAYRIlqOKFNDxVyKdNRFgnczDhHPy0t1iw',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent', [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
            ]);

            return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            Log::error('Exceção na chamada da IA: ' . $e->getMessage());
            return null;
        }
    }

    public function analyzeHabits(Request $request)
    {
        $user = Auth::user();

        $habits = $user->habits()->with('logs')->get();

        if ($habits->isEmpty()) {
            return response()->json([
                'analysis' => 'Você ainda não possui hábitos cadastrados. Que tal começar criando alguns?',
                'suggestions' => []
            ]);
        }

        // Preparar dados para a IA
        $habitsData = $habits->map(function($habit) {
            return [
                'title' => $habit->title,
                'description' => $habit->description,
                'frequency' => $habit->frequency->value,
                'total_logs' => $habit->logs->count(),
                'recent_completion' => $habit->logs->where('is_done', true)->count()
            ];
        });

        $prompt = $this->buildAnalysisPrompt($habitsData, $request->input('focus', 'geral'));

        $aiResponse = $this->callAI($prompt);

        if (!$aiResponse) {
            return response()->json([
                'analysis' => 'Desculpe, não consegui analisar seus hábitos no momento.',
                'suggestions' => $this->getFallbackSuggestions($habits)
            ], 500);
        }

        return response()->json([
            'analysis' => $aiResponse,
            'habits_count' => $habits->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Sugestões inteligentes de novos hábitos
     */
    public function suggestHabits(Request $request)
    {
        $user = Auth::user();
        $currentHabits = $user->habits()->get();

        $userContext = [
            'current_habits' => $currentHabits->pluck('title')->toArray(),
            'goals' => $request->input('goals', 'produtividade e bem-estar'),
            'available_time' => $request->input('available_time', 'moderate'),
            'difficulty' => $request->input('difficulty', 'iniciante')
        ];

        $prompt = $this->buildSuggestionPrompt($userContext);
        $aiResponse = $this->callAI($prompt);

        if (!$aiResponse) {
            return response()->json([
                'suggestions' => $this->getFallbackHabitSuggestions(),
                'note' => 'Sugestões geradas localmente'
            ]);
        }

        return response()->json([
            'suggestions' => $aiResponse,
            'based_on' => $userContext,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * IA analisa e cria hábitos automaticamente
     */
    public function createSmartHabits(Request $request)
    {
        $request->validate([
            'goals' => 'sometimes|string',
            'auto_create' => 'sometimes|boolean' // Se deve criar automaticamente
        ]);

        $user = Auth::user();
        $autoCreate = $request->input('auto_create', false);

        // Buscar hábitos existentes
        $currentHabits = $user->habits()->with('logs')->get();

        // Gerar prompt para sugestões específicas
        $prompt = $this->buildCreationPrompt($currentHabits, $request->input('goals', ''));

        $aiResponse = $this->callAI($prompt);

        if (!$aiResponse) {
            return response()->json([
                'error' => 'Não foi possível criar o hábito no momento'
            ], 500);
        }

        $habitsData = $this->parseAIHabitsResponse($aiResponse);

        $habits = [];


        foreach ($habitsData as $habitData) {
            $habits[] = Habit::create([
                'user_id' => Auth::id(),
                ...$habitData,
            ]);
        }

        return response()->json([
            'message' => 'Hábito criado com sucesso!',
            'habit' => $habits,
        ], 201);
    }

    private function buildCreationPrompt($currentHabits, $goals)
    {
        $habitsList = $currentHabits->pluck('title')->implode(', ');

        return "Com base nos hábitos atuais do usuário: {$habitsList} " .
               "e objetivos: {$goals}. Sugira 3 novos hábitos específicos.\n\n" .
               "Forneça em formato JSON:\n" .
               "[\n" .
               "  {\n" .
               "    \"title\": \"Título curto\",\n" .
               "    \"description\": \"Descrição detalhada\",\n" .
               "    \"frequency\": \"daily|weekly\",\n" .
               "  }\n" .
               "]\n\n" .
               "Seja prático e realista!";
    }

    private function buildAnalysisPrompt($habitsData, $focus)
    {
        return "Analise estes hábitos e forneça insights úteis:\n\n" .
               "Hábitos do usuário: " . json_encode($habitsData) . "\n\n" .
               "Foco da análise: " . $focus . "\n\n" .
               "Forneça:\n1. Análise geral do progresso\n2. Pontos fortes\n3. Sugestões de melhoria\n4. Recomendações específicas\n\n" .
               "Seja encorajador e prático!";
    }

    private function buildSuggestionPrompt($context)
    {
        return "Sugira 3-5 novos hábitos baseados neste contexto:\n\n" .
               "Hábitos atuais: " . implode(', ', $context['current_habits']) . "\n" .
               "Objetivos: " . $context['goals'] . "\n" .
               "Tempo disponível: " . $context['available_time'] . "\n" .
               "Nível: " . $context['difficulty'] . "\n\n" .
               "Forneça sugestões práticas e realistas, com explicação breve de cada uma.";
    }

    private function parseAIHabitsResponse($aiResponse)
    {
        try {
            // Método 1: Tenta decodificar diretamente se for JSON válido
            $habits = json_decode($aiResponse, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($habits)) {
                return $this->validateHabitsArray($habits);
            }

            // Método 2: Limpa a string e tenta novamente
            $cleaned = $this->cleanJsonResponse($aiResponse);
            $habits = json_decode($cleaned, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($habits)) {
                return $this->validateHabitsArray($habits);
            }

            // Método 3: Extrai JSON usando regex
            if (preg_match('/\[\s*\{[\s\S]*\}\s*\]/', $aiResponse, $matches)) {
                $habits = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($habits)) {
                    return $this->validateHabitsArray($habits);
                }
            }

            // Método 4: Fallback para parsing manual
            return $this->parseTextHabitsResponse($aiResponse);

        } catch (\Exception $e) {
            Log::error('Erro parsing IA: ' . $e->getMessage());
            return [];
        }
    }

    private function parseTextHabitsResponse($text)
    {
        // Parsing básico para resposta em texto puro
        $lines = explode("\n", $text);
        $habits = [];
        $currentHabit = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\.?\s*(.+)/', trim($line), $matches)) {
                if (!empty($currentHabit)) {
                    $habits[] = $currentHabit;
                }
                $currentHabit = ['title' => $matches[2], 'description' => ''];
            } elseif (!empty($currentHabit) && trim($line)) {
                $currentHabit['description'] .= ' ' . trim($line);
            }
        }

        if (!empty($currentHabit)) {
            $habits[] = $currentHabit;
        }

        return array_slice($habits, 0, 3); // Limita a 3 hábitos
    }

    private function cleanJsonResponse($response)
    {
        // Remove escapes desnecessários
        $cleaned = stripslashes($response);
        // Remove quebras de linha escapadas
        $cleaned = str_replace(['\n', '\t', '\r'], '', $cleaned);
        // Mantém apenas as quebras de linha reais para formatação
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return trim($cleaned);
    }

    private function validateHabitsArray($habits)
    {
        if (!is_array($habits)) return [];

        return array_map(function($habit) {
            if (!is_array($habit)) return null;

            return [
                'title' => $habit['title'] ?? $habit['name'] ?? 'Novo Hábito',
                'description' => $habit['description'] ?? $habit['desc'] ?? '',
                'frequency' => $this->validateFrequency($habit['frequency'] ?? 'daily'),
                'reason' => $habit['reason'] ?? $habit['benefit'] ?? 'Sugerido pela IA'
            ];
        }, array_filter($habits));
    }

    private function validateFrequency($frequency)
    {
        $validFrequencies = ['daily', 'weekly', 'monthly'];
        return in_array(strtolower($frequency), $validFrequencies) ? $frequency : 'daily';
    }

    private function getFallbackSuggestions($habits)
    {
        return [
            'Dica: Mantenha consistência nos hábitos que já possui',
            'Sugestão: Tente adicionar 1 hábito novo por semana',
            'Lembrete: Celebre pequenas vitórias diárias'
        ];
    }

    private function getFallbackHabitSuggestions()
    {
        return [
            [
                'title' => 'Meditação 5 minutos',
                'description' => 'Meditação curta para começar o dia',
                'frequency' => 'daily',
                'reason' => 'Ajuda na clareza mental e redução do estresse'
            ],
            [
                'title' => 'Leitura 15 minutos',
                'description' => 'Leitura de livro ou artigo educativo',
                'frequency' => 'daily',
                'reason' => 'Desenvolve conhecimento e hábito de aprendizado'
            ]
        ];
    }
}
