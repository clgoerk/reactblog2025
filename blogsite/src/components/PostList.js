import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import {useAuth} from "../context/AuthContext";
 
function PostList() {
  const [posts, setPosts] = useState([]);
  const [isLoading, setIsLoading] = useState(true); // was [true]
  const [error, setError] = useState('');
  const [currentPage, setCurrentPage] = useState('1');
  const [totalPosts, setTotalPosts] = useState('0');
  const postsPerPage = 4;
  const { user } = useAuth();
 
  const fetchPosts = useCallback(async () => {
    setIsLoading(true);
    try {
      const response = await axios.get(
        `${process.env.REACT_APP_API_BASE_URL}/posts.php?page=${currentPage}`,
        { withCredentials: true }
      );
      setPosts(response.data.posts);
      setTotalPosts(response.data.totalPosts);
    } catch (error) {
      console.error(error);
      setError("Failed to load posts.");
    } finally {
      setIsLoading(false);
    }
  }, [currentPage]);
 
  useEffect(() => {
    fetchPosts();
  }, [fetchPosts]);
 
  const handleDelete = async (id) => {
    if (!window.confirm("Are you sure you want to delete this post?")) return;
 
    try {
      await axios.post(
        `${process.env.REACT_APP_API_BASE_URL}/delete-post.php`,
        { id },
        { withCredentials: true }
      );
      // refresh list after deletion
      fetchPosts();
    } catch (error) {
      console.error(error);
      alert("Failed to delete post.");
    }
  };
 
  const totalPages = Math.ceil(totalPosts / postsPerPage);
  const goToPreviousPage = () => setCurrentPage(currentPage - 1);
  const goToNextPage = () => setCurrentPage(currentPage + 1);
 
  return (
    <div className="container mt-5">
      <h2 className="mb-5 ">Recent Posts</h2>
      {error && <div className="alert alert-danger">{error}</div>}
      <div className="row">
        {isLoading ? (
          <p>Loading posts...</p>
        ) : posts.length ? (
          posts.map(post => (
            <div className="col-md-6" key={post.id}>
              <div className="card mb-4 shadow-lg border-0">
                <div className="card-body">
                  <h5 className="card-title">{post.title}</h5>
                  <p className="card-text">By {post.author} on { new Date(post.publish_date).toLocaleDateString()}</p>
                  <Link to={`/post/${post.id}`} className="btn btn-primary text-light me-2">Read More</Link>
                   {user?.role === 'admin' && (
                    <> 
                  <Link to={`/edit/${post.id}`} className="btn btn-light text-dark me-2">Edit</Link>
                  <button
                    className="btn btn-danger"
                    onClick={() => handleDelete(post.id)}
                  >
                    Delete
                  </button>
                  </>
                   )}
                </div>
              </div>    
            </div>    
          ))
        ) : (
          <p>No posts available.</p>
        )}
      </div>            
      {/* Pagination Code */}
      <nav aria-label="Page navigation">
        <ul className="pagination justify-content-center mt-4">
          <li  className={`page-item ${currentPage === 1 ? 'disabled' : ''}`}>
            <button className="page-link text-dark" onClick={goToPreviousPage}>Previous</button>
          </li>
          {Array.from({ length: totalPages }, (_, index) => (
            <li key={index} className={`page-item ${index + 1 === currentPage ? 'bg-dark' : ''}`}>
              <button className="page-link text-dark" onClick={() => setCurrentPage(index + 1)}>{index + 1}</button>
            </li>
          ))}
          <li  className={`page-item ${currentPage === totalPages ? 'disabled' : ''}`}>
            <button className="page-link text-dark" onClick={goToNextPage}>Next</button>
          </li>
        </ul>
      </nav>
    </div>
  );
}
 
export default PostList;