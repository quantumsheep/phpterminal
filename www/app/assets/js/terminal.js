const conn = new WebSocket('ws://localhost:800');

conn.onopen = (e) => {
    console.log("Connection established!");

    document.getElementById('terminal-input').addEventListener('keydown', (e) => {
        if (e.key == "Enter") {
            if (e.target.value && e.target.value.length > 0 && e.target.value.replace(/[ ]+/i, '').length > 0) {
                e.target.value = e.target.value.replace(/^(\s+)?(.*?)(\s+)?$/, "$2");

                conn.send(e.target.value);
            }
        }
    });
};

conn.onmessage = (e) => {
    console.log(e.data);
};