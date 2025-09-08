import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

function CreatePost() {

    const [title, setTitle] = useState('');
    const [content, setContent] = useState('');
    const [author, setAuthor] = useState('');
    const [image, setImage] = useState(null);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');

    const navigate = useNavigate();

    // Validation
    const validateForm = () => {
        if (!title.trim() || !content.trim() || !author.trim()) {
            setError("Please fill in all the fields.");
            return false;
        }
        return true;
    }

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');
        if (!validateForm()) return;

        setIsLoading(true);

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);
            formData.append('author', author);
            if (image) formData.append('image', image);

            const response = await axios.post(
                `${process.env.REACT_APP_API_BASE_URL}/create-post.php`,
                formData,
                {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    withCredentials: true
                }
            );


            console.log(response.data);
            navigate('/');
        } catch (err) {
            console.error(err);
            setError('Failed to create post. Please try again.');
        } finally {
            setIsLoading(false);
        }
    }

    return (
        <div className="container mt-4">
            <h2>Create a New Post</h2>
            {error && <div className="alert alert-danger">{error}</div>}
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label className="form-label">Title</label>
                    <input type="text" className="form-control" onChange={e => setTitle(e.target.value)} required />
                </div>
                <div className="mb-3">
                    <label className="form-label">Content</label>
                    <input type="text" className="form-control" onChange={e => setContent(e.target.value)} required />
                </div>
                <div className="mb-3">
                    <label className="form-label">Author</label>
                    <input type="text" className="form-control" onChange={e => setAuthor(e.target.value)} required />
                </div>
                <div className="mb-3">
                    <label className="form-label">Author Image</label>
                    <input type="file" className="form-control" accept="image/*" onChange={e => setImage(e.target.files[0])} />
                </div>
                <button type="submit" className="btn btn-primary" disabled={isLoading}>
                    {isLoading ? 'Creating post...' : 'Create Post'}
                </button>
            </form>
        </div>
    );
}

export default CreatePost;

