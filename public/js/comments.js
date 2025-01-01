document.addEventListener('DOMContentLoaded', () => {
    const likeButtons = document.querySelectorAll('.like-button');
    const dislikeButtons = document.querySelectorAll('.dislike-button');

    likeButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            const commentId = button.dataset.commentId;
            try {
                const response = await fetch(`/comment/${commentId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || 'Une erreur est survenue');
                }

                const data = await response.json();
                updateLikeDislikeCounts(commentId, data.likes, data.dislikes);
            } catch (error) {
                alert(error.message);
            }
        });
    });

    dislikeButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            const commentId = button.dataset.commentId;
            try {
                const response = await fetch(`/comment/${commentId}/dislike`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || 'Une erreur est survenue');
                }

                const data = await response.json();
                updateLikeDislikeCounts(commentId, data.likes, data.dislikes);
            } catch (error) {
                alert(error.message);
            }
        });
    });

    function updateLikeDislikeCounts(commentId, likes, dislikes) {
        const likeCountElement = document.querySelector(`#like-count-${commentId}`);
        const dislikeCountElement = document.querySelector(`#dislike-count-${commentId}`);

        if (likeCountElement) likeCountElement.textContent = likes;
        if (dislikeCountElement) dislikeCountElement.textContent = dislikes;
    }
});
