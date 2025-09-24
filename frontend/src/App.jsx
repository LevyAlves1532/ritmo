import { createBrowserRouter, RouterProvider } from "react-router";

import Home from "./pages/home";
import Login from "./pages/login";

function App() {
  const router = createBrowserRouter([
    { index: true, Component: Home },
    { 
      Component: Login,
      path: '/login',
    },
  ]);

  return (
    <div className="">
      <RouterProvider 
        router={router}
      />
    </div>
  );
}

export default App;
