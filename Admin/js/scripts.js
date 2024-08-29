document.addEventListener('DOMContentLoaded', () => {
    const deleteLinks = document.querySelectorAll('a.delete');
    
    deleteLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            const confirmation = confirm('Are you sure you want to delete this order?');
            if (!confirmation) {
                event.preventDefault();
            }
        });
    });
});

// เปิดและปิดโมดัล
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('myModal');
    var img = document.querySelector('.view-payment-slip');
    var modalImg = document.getElementById('img01');
    var captionText = document.getElementById('caption');

    if (img) {
        img.onclick = function () {
            modal.style.display = 'block';
            modalImg.src = this.getAttribute('data-image');
            captionText.innerHTML = this.innerHTML;
        };
    }

    var span = document.getElementsByClassName('close')[0];
    span.onclick = function () {
        modal.style.display = 'none';
    };
});

