document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("nav-search-input");

    if (!input) return;

    // サジェスト用コンテナ生成
    const suggestionBox = document.createElement("div");
    suggestionBox.style.position = "absolute";
    suggestionBox.style.background = "#fff";
    suggestionBox.style.border = "1px solid #ccc";
    suggestionBox.style.width = input.offsetWidth + "px";
    suggestionBox.style.zIndex = 1000;
    input.parentNode.appendChild(suggestionBox);

    input.addEventListener("keyup", () => {
        const q = input.value;

        if (q.length === 0) {
            suggestionBox.innerHTML = "";
            return;
        }

        fetch(`/suggest.php?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                suggestionBox.innerHTML = "";
                data.forEach(name => {
                    const item = document.createElement("div");
                    item.textContent = name;
                    item.style.padding = "5px";
                    item.style.cursor = "pointer";

                    item.addEventListener("click", () => {
                        input.value = name;
                        suggestionBox.innerHTML = "";
                    });

                    suggestionBox.appendChild(item);
                });
            });
    });
});
