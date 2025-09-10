import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import axios from "axios";
 
const EditPost = () => {
  const { id } = useParams();
  const navigate = useNavigate();
 
  const [title, setTitle] = useState("");
  const [content, setContent] = useState("");
  const [author, setAuthor] = useState("");
  const [image, setImage] = useState(null); 
  const [error, setError] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
 
  useEffect(() => {
    const fetchPost = async () => {
      try {
        const res = await axios.get(
          `${process.env.REACT_APP_API_BASE_URL}/post.php/post/${id}`,
          { withCredentials: true }
        );
        const post = res.data.data;
        setTitle(post.title);
        setContent(post.content);
        setAuthor(post.author);
        setImage(post.image); // store existing image URL
      } catch (err) {
        setError("Failed to fetch post details.");
      }
    };
    fetchPost();
  }, [id]);
 
  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setIsLoading(true);
 
    try {
      const formData = new FormData();
      formData.append("id", id);
      formData.append("title", title);
      formData.append("content", content);
      formData.append("author", author);
 
      if (image && typeof image !== "string") {
        // only append if it's a new uploaded file
        formData.append("image", image);
      }
 
      await axios.post(
        `${process.env.REACT_APP_API_BASE_URL}/update-post.php`,
        formData,
        { withCredentials: true }
      );
 
      navigate(`/post/${id}`);
    } catch (err) {
      setError("Failed to update post.");
    } finally {
      setIsLoading(false);
    }
  };
 
  return (
    <div className="container mt-4 p-4 bg-light rounded shadow-lg mt-5 border-0">
      <h2 className="mb-5">Edit Post</h2>
      {error && <div className="alert alert-danger">{error}</div>}
 
      <form onSubmit={handleSubmit}>
        {/* Title */}
        <div className="row mb-3 align-items-center">
          <label htmlFor="title" className="col-sm-2 col-form-label fw-semibold">
            Title
          </label>
          <div className="col-sm-10">
            <input
              type="text"
              className="form-control w-50"
              id="title"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Enter post title"
              required
            />
          </div>
        </div>
 
        {/* Content */}
        <div className="row mb-3 align-items-center">
          <label htmlFor="content" className="col-sm-2 col-form-label fw-semibold">
            Content
          </label>
          <div className="col-sm-10">
            <textarea
              className="form-control w-50"
              id="content"
              rows="5"
              value={content}
              onChange={(e) => setContent(e.target.value)}
              placeholder="Enter post content"
              required
            />
          </div>
        </div>
 
        {/* Author */}
        <div className="row mb-3 align-items-center">
          <label htmlFor="author" className="col-sm-2 col-form-label fw-semibold">
            Author
          </label>
          <div className="col-sm-10">
            <input
              type="text"
              className="form-control w-50"
              id="author"
              value={author}
              onChange={(e) => setAuthor(e.target.value)}
              placeholder="Enter author name"
              required
            />
          </div>
        </div>
 
        {/* Image Upload */}
        <div className="row mb-3 align-items-center">
          <label htmlFor="image" className="col-sm-2 col-form-label fw-semibold">
            Post Image
          </label>
          <div className="col-sm-10">
            <input
              type="file"
              className="form-control w-50"
              id="image"
              accept="image/*"
              onChange={(e) => setImage(e.target.files[0])}
            />
            {image && (
              <img
                src={
                  typeof image === "string"
                    ? `${process.env.REACT_APP_API_BASE_URL}/uploads/${image}` // backend path
                    : URL.createObjectURL(image)
                }
                alt="Preview"
                className="img-thumbnail mt-2"
                style={{ maxWidth: "150px" }}
              />
            )}
          </div>
        </div>
 
        {/* Submit and Cancel Buttons */}
        <div className="text-end">
          <button 
            type="submit" 
            className="btn btn-primary me-2" 
            disabled={isLoading}
          >
            {isLoading ? (
              <span>
                <span
                  className="spinner-border spinner-border-sm me-2"
                  role="status"
                  aria-hidden="true"
                ></span>
                Saving changes...
              </span>
            ) : (
              "Save Changes"
            )}
          </button>

          <button 
            type="button" 
            className="btn btn-danger"
            onClick={() => navigate('/')}
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  );
};
 
export default EditPost;
 
