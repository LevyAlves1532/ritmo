import { useState } from "react";

function Login() {
  const [ email, setEmail ] = useState("");
  const [ password, setPassword ] = useState("");

  const handleSubmit = () => {

  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-base-200">
      <div className="card w-96 shadow-xl bg-base-100">
        <div className="card-body">
          <h2 className="card-title justify-center mb-5">Login</h2>
          <form onSubmit={handleSubmit} className="form-control flex flex-col gap-4">
            <input
              type="email"
              placeholder="Email"
              className="input input-bordered w-full"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
            <input
              type="password"
              placeholder="Senha"
              className="input input-bordered w-full"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
            <button type="submit" className="btn btn-primary w-full mt-5">
              Entrar
            </button>
          </form>
          <div className="text-center mt-2">
            <a href="/register" className="link link-primary">
              Criar conta
            </a>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Login
