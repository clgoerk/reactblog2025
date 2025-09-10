import React from 'react';
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import { AuthProvider } from "./context/AuthContext";
import ProtectedRoute from "./components/ProtectedRoute";
import Login from "./components/Login";
import Register from "./components/Register";
import './App.css';
import Navbar from './components/Navbar';
import CreatePost from './components/CreatePost';
import Post from './components/Post';
import PostList from './components/PostList';
import EditPost from "./components/EditPost";

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />

          {/* Protected route for editing posts */}
          <Route
            path="/edit/:id"
            element={
              <ProtectedRoute>
                <EditPost />
              </ProtectedRoute>
            }
          />

          {/* Protected routes include Navbar */}
          <Route
            path="/"
            element={
              <ProtectedRoute>
                <>
                  <Navbar />   {/* ✅ Only logged-in users see it */}
                  <PostList />
                </>
              </ProtectedRoute>
            }
          />
          <Route
            path="/post/:id"
            element={
              <ProtectedRoute>
                <>
                  <Navbar />
                  <Post />
                </>
              </ProtectedRoute>
            }
          />
          <Route
            path="/create"
            element={
              <ProtectedRoute>
                <>
                  <Navbar />
                  <CreatePost />
                </>
              </ProtectedRoute>
            }
          />
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default App;


