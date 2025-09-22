<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verifique seu email - Ritmo</title>
        <style>
            /* Reset */
            body, p, h1, a {
                margin: 0;
                padding: 0;
            }
            body {
                background-color: #fdfaf8;
                font-family: Arial, Helvetica, sans-serif;
                color: #333;
                font-size: 16px;
                line-height: 1.5;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background: #ffffff;
                padding: 20px;
                text-align: center;
                border-radius: 8px;
            }
            .logo {
                margin-bottom: 20px;
            }
            .logo img {
                width: 200px;
            }
            .banner {
                background: #8AFF8A;
                padding: 30px 20px;
                border-radius: 8px;
                margin-bottom: 30px;
            }
            .banner img {
                width: 400px;
            }
            h1 {
                font-size: 22px;
                font-weight: bold;
                margin-bottom: 15px;
            }
            p {
                margin-bottom: 20px;
                color: #555;
            }
            .btn {
                display: inline-block;
                background: #00CF00;
                color: #000 !important;
                text-decoration: none;
                font-weight: bold;
                padding: 14px 24px;
                border-radius: 30px;
                font-size: 16px;
            }
            .btn:hover {
                background: #00AD00;
            }
            .footer {
                font-size: 13px;
                color: #777;
                margin-top: 30px;
                text-align: center;
            }
            .footer a {
                color: #00AD00;
                text-decoration: none;
            }
            .social {
                margin: 20px 0;
            }
            .social a {
                margin: 0 8px;
                text-decoration: none;
                font-size: 18px;
                color: #555;
            }
            @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            h1 {
                font-size: 20px;
            }
            .btn {
                padding: 12px 20px;
                font-size: 15px;
            }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Logo -->
            <div class="logo">
                <img src="{{ asset('assets/images/ritmo.png') }}" alt="Ritmo">
            </div>

            <!-- Banner -->
            <div class="banner">
                <img src="{{ asset('assets/images/confirmed.png') }}" alt="Ritmo Banner">
            </div>

            <!-- Conteúdo -->
            <h1>Verifique seu endereço de email</h1>
            <p>Você está a um passo de começar sua jornada no Ritmo! Para finalizar o cadastro, clique no botão abaixo para confirmar seu email. O link será válido pelas próximas 24 horas.</p>

            <!-- Botão -->
            <a href="{{ route('user.confirm-account', ['user' => $user]) }}" target="_blank" class="btn">Verificar meu email</a>

            <!-- Rodapé -->
            <div class="footer">
                <p>Se tiver dúvidas, acesse nossas <a href="#">FAQs</a> ou envie um email para <a href="mailto:suporte@ritmo.com">suporte@ritmo.com</a>.</p>

                <div class="social">
                    <a href="#">🌐</a>
                    <a href="#">📸</a>
                    <a href="#">🐦</a>
                    <a href="#">▶️</a>
                </div>

                <p>Ritmo © 2025. Todos os direitos reservados.</p>
            </div>
        </div>
    </body>
</html>
