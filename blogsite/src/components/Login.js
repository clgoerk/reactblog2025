// src/components/Login.js
import React, { useState, useContext } from "react";
import { useNavigate, Link } from "react-router-dom";
import axios from "axios";
import { AuthContext } from "../context/AuthContext";

function Login() {
  const [userName, setUserName] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const { setUser } = useContext(AuthContext);
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
     try {
    const res = await axios.post(
      `${process.env.REACT_APP_API_BASE_URL}/login.php`,
      { userName, password },
      { withCredentials: true }
    );
    console.log("Login response:", res.data); //Add this
    if (res.data.success) {
      setUser(res.data.user);
      navigate("/");
    } else {
      setError(res.data.message || "Invalid credentials");
    }
  } catch (err) {
    console.error("Axios login error:", err);
    setError("Login failed");
  }

  };

  return (
    <div className="container mt-4">
      <h2>Login</h2>
      {error && <div className="alert alert-danger">{error}</div>}
      <form onSubmit={handleSubmit}>
        <div className="mb-3">
          <label>Username</label>
          <input
            type="text"
            className="form-control"
            value={userName}
            onChange={(e) => setUserName(e.target.value)}
            required
          />
        </div>
        <div className="mb-3">
          <label>Password</label>
          <input
            type="password"
            className="form-control"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </div>
        <button className="btn btn-primary" type="submit">Login</button>
      </form>
      <p className="mt-3">
        Don’t have an account? <Link to="/register">Register</Link>
      </p>
    </div>
  );
}

export default Login;
