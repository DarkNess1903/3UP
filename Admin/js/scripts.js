// Modal script for viewing payment slip
document.addEventListener('DOMContentLoaded', function () {
    
    const modal = document.getElementById("myModal");
    const img = document.getElementById("img01");
    const captionText = document.getElementById("caption");

    document.querySelectorAll('.view-payment-slip').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            modal.style.display = "block";
            img.src = this.getAttribute('data-image');
            captionText.innerHTML = this.innerText;
        });
    });

    document.querySelector('.close').onclick = function() {
        modal.style.display = "none";
    };
});
