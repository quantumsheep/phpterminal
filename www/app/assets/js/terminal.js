const conn = new WebSocket('ws://localhost:800');
let HistoryCmd = [""];
let HistoryCount = 0;
let HistoryCounter = 0;
const termContainer = document.getElementById("terminal-container");
let ClickCount = 0;

conn.onopen = (e) => {
    console.log("Connection established!");

    document.getElementById('terminal-input').addEventListener('keydown', (e) => {
        if (e.key == "Enter") {
            e.preventDefault();
            if (e.target.innerHTML && e.target.innerHTML.length > 0 && e.target.innerHTML.replace(/[ ]+/i, '').length > 0) {
                e.target.innerHTML = e.target.innerHTML.replace(/^(\s+)?(.*?)(\s+)?$/, "$2");
                if (e.target.innerHTML != HistoryCmd[HistoryCounter - 1]) {
                    HistoryCmd[HistoryCounter] = e.target.innerHTML;
                    HistoryCounter++;
                    HistoryCount++;
                }
                conn.send(e.target.innerHTML);
                appendTerminal(`user@user:~ $ ${e.target.innerHTML}`);
                e.target.innerHTML = "";
            }
        }
    });
};

document.getElementById('terminal-input').addEventListener('keydown', (e) => {
    if (e.key == "ArrowUp") {
        document.getElementById('terminal-input').addEventListener('keydown', (e) => {
            if (e.key == "Enter") {
                HistoryCount = HistoryCmd.length;
            }
        });
        HistoryCount--;
        if (HistoryCount < 1) {
            HistoryCount = 0;
        }
        document.getElementById('terminal-input').innerHTML = `${HistoryCmd[HistoryCount]}`;
    } else if (e.key == "ArrowDown") {
        document.getElementById('terminal-input').addEventListener('keydown', (e) => {
            if (e.key == "Enter") {
                HistoryCount = HistoryCmd.length;
            }
        });
        HistoryCount++;
        if (HistoryCount > HistoryCmd.length - 1) {
            HistoryCount = HistoryCmd.length;
            document.getElementById('terminal-input').innerHTML = "";
        } else {
            document.getElementById('terminal-input').innerHTML = `${HistoryCmd[HistoryCount]}`;
        }
    } else if (e.key == "Escape") {
        document.getElementById('terminal-input').innerHTML = "";
    }
});

function move() {
    console.log("Move");
};

function click() {
    ClickCount++;
    if (ClickCount == 1) {
        singleClickTimer = setTimeout((f) => {
            const input = document.getElementById('terminal-input');

            let range;
            let selection;

            if (document.createRange) //Firefox, Chrome, Opera, Safari, IE 9+
            {
                range = document.createRange();
                range.selectNodeContents(input);
                range.collapse(false);
                selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
            }
            else if (document.selection) //IE 8 and lower
            {
                range = document.body.createTextRange();
                range.moveToElementText(input);
                range.collapse(false);
                range.select();
            }
            ClickCount = 0;
        }, 200);
    } else if (ClickCount > 1) {
        clearTimeout(singleClickTimer);
        ClickCount = 0;
    }
};

termContainer.addEventListener("click", click, false);

termContainer.addEventListener("mousedown", e => {
    termContainer.addEventListener("click", click, false);
    mouseDown = setTimeout((f) => {
        termContainer.addEventListener("mousemove", move, false);
        termContainer.removeEventListener("click", click, false);

    }, 200);
    termContainer.addEventListener("mouseup", e => {
        clearTimeout(mouseDown);
        termContainer.removeEventListener("mousemove", move, false);
    });
});

conn.onmessage = (e) => {
    appendTerminal(e.data);
    termContainer.scrollTo(0, termContainer.scrollHeight);
};

function appendTerminal(text) {
    document.getElementById("terminal-content-user").innerHTML = `${document.getElementById("terminal-content-user").innerHTML}<div>${text}</div>`;
}