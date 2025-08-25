import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

function CreatePost() {

  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [author, setAuthor] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  const navigate = useNavigate();

  // Validtion function
  const validateForm = () => {
    if (!title.trim() || !content.trim() || !author.trim()) {
      setError("Please fill in all the fields.");
      return false;
    }
    return true;
  }

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError(''); // reset error message on a new form submission
    if (!validateForm()) {
      return;
    }
    setIsLoading(true);
    try {
      const response = await axios.post(`${process.env.REACT_APP_API_BASE_URL}/create-post.php`, {
        title,
        content,
        author     
    });
    console.log(response.data);
    navigate('/');
    } catch (error) {
        console.error(error);
        setError('Failed to create post. Please try again later.');
        setIsLoading(false);
    } 
  }
  
  return(
    <div className="container mt-4">
      <h2>Create a New Post</h2>
      {error && <div className="alert alert-danger" role="alert">{error}</div>}
      <form onSubmit={handleSubmit}>
        <div className="mb-3">
          <label htmlFor="title" className="form-label">
            Title
          </label>
          <input
            type="text"
            className="form-control"
            id="title"
            onChange={(e) => setTitle(e.target.value)}
            required
          />
        </div>
        <div className="mb-3">
          <label htmlFor="content" className="form-label">
            Content
          </label>
          <input
            type="text"
            className="form-control"
            id="content"
            onChange={(e) => setContent(e.target.value)}
            required
          />
        </div>
        <div className="mb-3">
          <label htmlFor="author" className="form-label">
            Author
          </label>
          <input
            type="text"
            className="form-control"
            id="author"
            onChange={(e) => setAuthor(e.target.value)}
            required
          />
        </div>
        <button type="submit" className="btn btn-primary" disabled={isLoading}>
          {isLoading ? <span><span className="spinner-boarder spinner-boarder-sm" role="status" aria-hidden="true"></span>
          Creating post...</span> : 'Create Post'}
        </button>
      </form>
    </div>
    );
}

export default CreatePost;