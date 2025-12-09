// ---- サジェスト表示 ----
function suggest() {
    const key = document.getElementById("search").value;

    if (key === "") {
        document.getElementById("suggest-box").innerHTML = "";
        return;
    }

    fetch("suggest.php?keyword=" + encodeURIComponent(key))
        .then(res => res.json())
        .then(data => {
            let html = "";
            data.forEach(item => {
                html += `
                    <div class="suggest-item" onclick="setWord('${item.product_name}')">
                        ${item.product_name}
                    </div>
                `;
            });
            document.getElementById("suggest-box").innerHTML = html;
        });
}

// ---- 候補クリック時に検索ボックスへセット ----
function setWord(text) {
    document.getElementById("search").value = text;
    document.getElementById("suggest-box").innerHTML = "";
}
