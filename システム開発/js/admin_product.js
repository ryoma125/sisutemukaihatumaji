document.getElementById("imageInput").addEventListener("change", function(e) {
    const preview = document.getElementById("previewArea");
    preview.innerHTML = ""; // 古いプレビューを消す

    const files = e.target.files;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        if (!file.type.startsWith("image/")) continue;

        const reader = new FileReader();
        reader.onload = function(evt) {
            const img = document.createElement("img");
            img.src = evt.target.result;
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
});
