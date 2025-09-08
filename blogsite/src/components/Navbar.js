// src/components/Navbar.js
import React, { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import axios from "axios";

const Navbar = () => {
  const [username, setUsername] = useState("");
  const navigate = useNavigate();

  // Fetch logged-in user info
  useEffect(() => {
    const fetchUser = async () => {
      try {
        const response = await axios.get(
          `${process.env.REACT_APP_API_BASE_URL}/checkAuth.php`,
          { withCredentials: true }
        );

        if (response.data && response.data.authenticated) {
          setUsername(response.data.user.userName);
        } else {
          navigate("/login");
        }
      } catch (error) {
        console.error("Auth check failed", error);
        navigate("/login");
      }
    };

    fetchUser();
  }, [navigate]);

  // Logout handler
  const handleLogout = async () => {
    try {
      await axios.post(
        `${process.env.REACT_APP_API_BASE_URL}/logout.php`,
        {},
        { withCredentials: true }
      );
    } catch (error) {
      console.error("Logout failed", error);
    } finally {
      navigate("/login");
    }
  };

  return (
    <nav className="navbar navbar-expand-lg navbar-light bg-light mb-4 border-bottom shadow-sm">
      <div className="container">
        <Link className="navbar-brand fw-bold" to="/">
          BlogSite
        </Link>

        {/* Hamburger / Toggler */}
        <button
          className="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span className="navbar-toggler-icon"></span>
        </button>

        {/* Collapsible Navbar Content */}
        <div className="collapse navbar-collapse" id="navbarNav">
          <ul className="navbar-nav me-auto mb-2 mb-lg-0">
            <li className="nav-item">
              <Link className="nav-link" to="/">
                Posts
              </Link>
            </li>
            <li className="nav-item">
              <Link className="nav-link" to="/create">
                Create Post
              </Link>
            </li>
          </ul>

          {username && (
            <div className="d-flex align-items-center">
              <span className="navbar-text me-3">
                Logged in as <strong>{username}</strong>
              </span>
              <button className="btn btn-outline-dark" onClick={handleLogout}>
                Logout
              </button>
            </div>
          )}
        </div>
      </div>
    </nav>
  );
};

export default Navbar;