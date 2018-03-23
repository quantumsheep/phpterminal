const conn = new WebSocket('ws://localhost:800');
let HistoryCmd = [""];
let HistoryCount = 0;
let HistoryCounter = 0;

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

document.getElementById("terminal-container").addEventListener("mousedown", e => {

    function MouseMove() {
        console.log("move");
        //  document.getElementById("terminal-container").removeEventListener("mouseup", MouseUp, false);
    }

    function click(){
        console.log("click");
    }

    function MouseUp() {
        console.log("mouseup");
        clearTimeout(selectTimer);

        document.getElementById("terminal-container").removeEventListener("mousemove", MouseMove, false);

        let input = document.getElementById('terminal-input');

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
    }

    console.log("mousedown");

    selectTimer = setTimeout((e) => {
        document.getElementById("terminal-container").addEventListener("mousemove", MouseMove, false);
    }, 1000);

    document.getElementById("terminal-container").addEventListener("mouseup", MouseUp, false);
});

// document.getElementById('terminal-user').addEventListener('mousedown', (e) => {
//     MouseDown++;
//     if (MouseDown == 2) {
//         selectTimer = setTimeout((f) => {
//             document.getElementById('terminal-container').addEventListener('click', (e) => {
//                 ClickCount++;
//                 if (ClickCount == 1) {
//                     singleClickTimer = setTimeout((f) => {
//                         let input = document.getElementById('terminal-input');

//                         let range;
//                         let selection;

//                         if (document.createRange) //Firefox, Chrome, Opera, Safari, IE 9+
//                         {
//                             range = document.createRange();
//                             range.selectNodeContents(input);
//                             range.collapse(false);
//                             selection = window.getSelection();
//                             selection.removeAllRanges();
//                             selection.addRange(range);
//                         }
//                         else if (document.selection) //IE 8 and lower
//                         {
//                             range = document.body.createTextRange();
//                             range.moveToElementText(input);
//                             range.collapse(false);
//                             range.select();
//                         }
//                         ClickCount = 0;
//                     }, 200);
//                 } else if (ClickCount > 1) {
//                     clearTimeout(singleClickTimer);
//                     ClickCount = 0;
//                 }
//             }, 20);
//             selectingTimer = setTimeout((f) => {
//                 document.getElementById('terminal-user').addEventListener('mousemove', (e) => {
//                     CountDown++;
//                     console.log("TRIGGER2 : " + CountDown);
//                     if (CountDown == 2) {
//                         stopSelectClickTimer = setTimeout((f) => {
//                             document.getElementById('terminal-user').addEventListener('mouseup', (e) => {
//                                 console.log("TRIGGER3 : " + CountDown);
//                                 CountDown = 0;
//                             }, false);
//                         }, 10);
//                         CountDown == 0;
//                     } else if (CountDown == 0) {
//                         clearTimeout(selectClickTimer);
//                         clearTimeout(stopSelectClickTimer);
//                         CountDown = 0;
//                     }
//                 }, 200);
//             }, false);
//         }, false);
//     } else if (MouseDown > 1) {
//         clearTimeout(selectTimer);
//         clearTimeout(selectingTimer);
//         MouseDown = 0;
//     }
// }, false);

conn.onmessage = (e) => {
    appendTerminal(e.data);
    document.getElementById("terminal-container").scrollTo(0, document.getElementById("terminal-container").scrollHeight);
};

function appendTerminal(text) {
    document.getElementById("terminal-content-user").innerHTML = `${document.getElementById("terminal-content-user").innerHTML}<div>${text}</div>`;
}