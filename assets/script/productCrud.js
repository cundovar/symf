
    function openCustomModal(id) {
        const modal = document.getElementById('customEditModal' + id);
        if (modal) modal.style.display = 'block';
    }

    function closeCustomModal(id) {
        const modal = document.getElementById('customEditModal' + id);
        if (modal) modal.style.display = 'none';
    }

    // Fermer modale si on clique en dehors du contenu
    window.addEventListener('click', function (e) {
        document.querySelectorAll('.custom-modal').forEach(modal => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    console.log('productCrud')
