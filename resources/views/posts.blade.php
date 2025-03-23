<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Post Management</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <h2>Create Post</h2>
    <form id="createPostForm">
        @csrf
        <input type="text" name="title" placeholder="Title" required>
        <textarea name="body" placeholder="Body" required></textarea>
        <input type="file" name="image">
        <button type="submit">Create Post</button>
    </form>

    <hr>
    <h2>All Posts</h2>
    <div id="postsContainer">Loading posts...</div>

    <script>
        $(document).ready(function() {
            const token = localStorage.getItem('token');

            // التحقق من تسجيل الدخول
            if (!token) {
                alert("⚠️ You are not logged in!");
                return;
            }

            //  دالة لجلب المنشورات
            async function fetchPosts() {
                try {
                    let response = await fetch('/api/posts', {
                        method: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    let data = await response.json();

                    let postsContainer = $('#postsContainer');
                    postsContainer.html('');

                    if (data.data.length === 0) {
                        postsContainer.html('<p>No posts available.</p>');
                        return;
                    }

                    data.data.forEach(post => {
                        postsContainer.append(`
                            <div id="post-${post.id}">
                                <h3>${post.title}</h3>
                                <p>${post.body}</p>
                                <img src="${getImageUrl(post.image)}" alt="Post Image" style="max-width: 100%; height: auto;">
                                <button type='submit' onclick="deletePost(${post.id})">Delete</button>
                                <hr>
                            </div>
                        `);
                    });
                } catch (error) {
                    console.error(error);
                    $('#postsContainer').html('<p>Error loading posts.</p>');
                }
            }

            function getImageUrl(imagePath) {
                if (!imagePath) {
                    return 'default-image.jpg'; // ضع صورة افتراضية إذا لم يكن هناك صورة
                }
                if (imagePath.startsWith('http') || imagePath.startsWith('/storage')) {
                    return imagePath; // الصورة لديها مسار صحيح بالفعل
                }
                return `/storage/app/public/${imagePath}`; // تعديل المسار ليكون داخل مجلد `storage`
            }


            // استدعاء المنشورات عند تحميل الصفحة
            fetchPosts();

            // إنشاء منشور جديد
            $('#createPostForm').submit(async function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                try {
                    let response = await fetch('/api/posts', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    if (!response.ok) throw await response.json();

                    fetchPosts();
                } catch (error) {
                    alert('❌ Error: ' + (error.message || 'Something went wrong!'));
                }
            });
        });

        // delete post ajax
        async function deletePost(id) {
            if (!confirm('⚠️ Are you sure you want to delete this post?')) return;

            let token = localStorage.getItem('token');
            if (!token) {
                alert("Error: You are not logged in!");
                return;
            }

            try {
                let response = await fetch(`/api/posts/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (!response.ok) throw await response.json();


                alert('✅ Post Deleted Successfully!');
                $(`#post-${id}`).remove();
            } catch (error) {
                alert('❌ Error: ' + (error.message || 'Something went wrong!'));
            }
        }
    </script>

</body>

</html>
