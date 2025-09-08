import React, { useState } from "react";
import { useParams } from "react-router-dom";
import axios from "axios";
 
const Post = () => {
 
    const { id } = useParams();
    const [post, setPost] = useState(null);
 
    const fetchPost = async () => {
        try {
          const response = await axios.get(`${process.env.REACT_APP_API_BASE_URL}/post.php/post/${id}`,
            { withCredentials: true } // send PHP session cookie
          );
          const post = response.data.data;
          setPost(post);
        }
        catch (error) {
          console.log(error);
        }
    };
 
   const handleVote = async (type) => {
  try {
    await axios({
      method: "post",
      url: `${process.env.REACT_APP_API_BASE_URL}/vote.php`,
      headers: {
        "Content-Type": "application/json",
      },
      withCredentials: true, // ✅ add this
      data: {
        post_id: id,
        vote_type: type,
      },
    });
    // Refresh post after voting
    fetchPost();
  } catch (error) {
    console.error("Error submitting vote:", error);
  }
};
 
 
    React.useEffect(() => {
        fetchPost();
    });
 
    if (!post) {
        return <div>Loading...</div>;
    }
 
    return (
        <div className="container my-5">
            <h1 >{post.title}</h1>
            <div className="d-flex justify-content-center">
                <small className="text-muted">
                    Posted by {post.author} on {post.date}
                </small>
            </div>
            <hr />
            <p className="mt-5">{post.content}</p>
            {post.imageName && (
              <img
                src={`${process.env.REACT_APP_API_BASE_URL}/uploads/${post.imageName}`}
                alt={post.author}
                className="img mb-5"
                style={{ maxWidth: "150px",  maxHeight: "150px"}}
              />
            )}
 
            <div className="d-flex justify-content-center gap-3">
              <button className="btn btn-success" onClick={() => handleVote("like")}>
                <i className="bi bi-hand-thumbs-up"></i> {post.likes} Likes
              </button>
              <button className="btn btn-danger" onClick={() => handleVote("dislike")}>
                <i className="bi bi-hand-thumbs-down"></i> {post.dislikes} Dislikes
              </button>
            </div>
            
        </div>
    );
};
 
export default Post;
 
 

