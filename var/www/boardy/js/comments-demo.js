
const API = "https://api.armontex.ai-info.ru";
const POST_ID = 1;

async function loadItems() {
  const res = await fetch(`${API}/api/posts/${POST_ID}/comments`);
  const data = await res.json();

  document.getElementById("list").innerHTML = data.items
    .map(
      (item) => `
        <div>
          <strong>${esc(item.author_name)}</strong>
          <p>${esc(item.body)}</p>
        </div>
      `,
    )
    .join("");
}

loadItems();


document.getElementById("btn").addEventListener("click", async () => {
  const body = document.getElementById("body").value.trim();
  if (!body) return;

  await fetch(`${API}/api/posts/${POST_ID}/comments`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ body: body }),
  });

  document.getElementById("body").value = "";
  loadItems();
});


function esc(str) {
  const div = document.createElement("div");
  div.textContent = str;
  return div.innerHTML;
}
