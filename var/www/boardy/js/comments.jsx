const { useState, useEffect } = React;

const API = "";
const POST_ID = 1;

function ItemList() {
  const [items, setItems] = useState([]);
  const [text, setText] = useState("");
  const [editId, setEditId] = useState(null);
  const [editText, setEditText] = useState("");

  const load = async () => {
    const res = await fetch(`${API}/api/posts/${POST_ID}/comments`);
    const data = await res.json();
    setItems(data.items);
  };

  useEffect(() => {
    load();
  }, []);

  const add = async () => {
    if (!text.trim()) return;

    await fetch(`${API}/api/posts/${POST_ID}/comments`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ body: text }),
    });

    setText("");
    load();
  };

  const save = async (id) => {
    await fetch(`${API}/api/comments/${id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ body: editText }),
    });

    setEditId(null);
    load();
  };

  const del = async (id) => {
    if (!confirm("Удалить?")) return;

    await fetch(`${API}/api/comments/${id}`, { method: "DELETE" });
    load();
  };

  return (
    <div>
      {items.map((item) => (
        <div key={item.id} className="card mb-2">
          <div className="card-body">
            <strong>{item.author_name}</strong>

            {editId === item.id ? (
              <div className="input-group">
                <input
                  className="form-control"
                  value={editText}
                  onChange={(event) => setEditText(event.target.value)}
                />
                <button className="btn btn-success" onClick={() => save(item.id)}>
                  Сохранить
                </button>
                <button
                  className="btn btn-secondary"
                  onClick={() => setEditId(null)}
                >
                  Отмена
                </button>
              </div>
            ) : (
              <div>
                <p>{item.body}</p>
                <button
                  className="btn btn-sm btn-outline-secondary"
                  onClick={() => {
                    setEditId(item.id);
                    setEditText(item.body);
                  }}
                >
                  Редактировать
                </button>
                <button
                  className="btn btn-sm btn-outline-danger ms-2"
                  onClick={() => del(item.id)}
                >
                  Удалить
                </button>
              </div>
            )}
          </div>
        </div>
      ))}

      <div className="input-group mt-3">
        <input
          className="form-control"
          placeholder="Комментарий"
          value={text}
          onChange={(event) => setText(event.target.value)}
        />
        <button className="btn btn-primary" onClick={add}>
          Отправить
        </button>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById("app")).render(<ItemList />);
