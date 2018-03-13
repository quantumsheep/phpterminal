const conn = new WebSocket('ws://localhost:800');
let ClickCount = 0;

conn.onopen = (e) => {
    console.log("Connection established!");

    document.getElementById('terminal-input').addEventListener('keydown', (e) => {
        if (e.key == "Enter") {
            e.preventDefault();
            if (e.target.value && e.target.value.length > 0 && e.target.value.replace(/[ ]+/i, '').length > 0) {
                e.target.value = e.target.value.replace(/^(\s+)?(.*?)(\s+)?$/, "$2");
                conn.send(e.target.value);
                appendTerminal(`user@user:~ $ ${e.target.value}`);
                e.target.value = "";
            }
        }
    });
};

document.getElementById('terminal-container').addEventListener('select', (e) => {
    CheckClick = false;
    setTimeout(function () { CheckClick = true; }, 2000);
});

document.getElementById('terminal-container').addEventListener('click', (e) => {
    ClickCount++;
    if (ClickCount == 1) {
        singleClickTimer = setTimeout(function () {
            document.getElementById('terminal-input').focus();
            clickCount = 0;
            console.log("1click : " + ClickCount);
        }, 400);
    } else if (ClickCount > 1) {
        clearTimeout(singleClickTimer);
        ClickCount = 0;
        console.log("2click : " + ClickCount);
    }
}, false);

conn.onmessage = (e) => {
    appendTerminal(e.data);
    document.getElementById("terminal-container").scrollTo(0, document.getElementById("terminal-container").scrollHeight);
};

function appendTerminal(text) {
    document.getElementById("terminal-content-user").innerHTML = `${document.getElementById("terminal-content-user").innerHTML}<div>${text}</div>`;
}