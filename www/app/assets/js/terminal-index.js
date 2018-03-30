const config = [
    {
        timeout: 100,
        elemId: "terminal-input-test",
        data: "help"
    },
    {
        timeout: 50,
        elemId: "terminal-content-user-test",
        data: [
            "<div>help [-dms] [pattern ...]</div>",
            "<div>history [-c] [-d offset] [n] or history -anrw [filename] or history -p arg [arg...]</div>",
            "<div id='ID01'>user@user:~ $ </div>"
        ]
    },
    {
        timeout: 100,
        elemId: "ID01",
        data: "help history"
    },
    {
        timeout: 50,
        elemId: "terminal-content-user-test",
        data: [
            "<div>help [-dms] [pattern ...]</div>",
            "<div>history [-c] [-d offset] [n] or history -anrw [filename] or history -p arg [arg...]</div>",
            "<div>Display or manipulate the history list.</div>",
            "<div>Display the history list with line numbers, prefixing each modified entry with a `*'. An argument of N lists only the last N entries.</div>",
            "<div>Options:</div>",
            "<div>-c clear the history list by deleting all of the entries</div>",
            "<div>-d offset delete the history entry at position OFFSET.</div>",
            "<div>-a append history lines from this session to the history file</div>",
            "<div>-n read all history lines not already read from the history file and append them to the history list</div>",
            "<div>-r read the history file and append the contents to the history list</div>",
            "<div>-w write the current history to the history file</div>",
            "<div>-p perform history expansion on each ARG and display the result without storing it in the history list</div>",
            "<div>-s append the ARGs to the history list as a single entry</div>",
            "<div>Arguments:</div>",
            "<div>PATTERN If FILENAME is given, it is used as the history file. Otherwise, if HISTFILE has a value, that is used, else ~/.bash_history.</div>",
            "<div>If the HISTTIMEFORMAT variable is set and not null, its value is used as a format string for strftime(3) to print the time stamp associated with each displayed history entry. No time stamps are printed otherwise.</div>",
            "<div>Exit Status:</div>",
            "<div>Returns success unless an invalid option is given or an error occurs.</div>",
            "<div id='ID02'>user@user:~ $ </div>"
        ]
    },
    {
        timeout: 100,
        elemId: "ID02",
        data: "history"
    },
    {
        timeout: 50,
        elemId: "terminal-content-user-test",
        data: [
            "<div>1 help</div>",
            "<div>2 help history</div>",
            "<div>2 history</div>",
            "<div id='ID03'>user@user:~ $ </div>"
        ]
    },
    {
        timeout: 100,
        elemId: "ID03",
        data: "clear"
    },
    {
        timeout: 200,
        cmd: () => {
            document.getElementById("terminal-container-test").removeChild(document.getElementById("term"));
        }
    },
    {
        timeout: 100,
        elemId: "terminal-container-test",
        data: [
            "<div id='term1' class='term1'><center><p>Welcome to alPH</p><p>You can use this terminal as a demo.</p></center></div>"
        ]
    },
    {
        timeout: 200,
        cmd2: () => {
            document.getElementById("term1").classList.remove("term1");
        }
    },
    {
        timeout: 2000,
        cmd3: () => {
            document.getElementById("term1").classList.add("term1");
        }
    },
    {
        timeout: 2500,
        cmd4: () => {
            document.getElementById("term-test").removeChild(document.getElementById("terminal-container-test"));
            document.getElementById("term-test").classList.add("shy");
            document.getElementById("terminal-container-exemple").classList.remove("shy");
        }
    }
];
const config2 = [
    {
        timeout: 9500,
        elemId: "terminal-content-user-exemple",
        data: [
            "<div id='ID04'>Login as : </div>"
        ]
    },
    {
        timeout: 1000,
        elemId: "",
        data: ""
    },
    {
        timeout: 100,
        elemId: "ID04",
        data: "anonymous"
    },
    {
        timeout: 200,
        elemId: "terminal-content-user-exemple",
        data: [
            "<div>You are now connected as Anonymous.</div>"
        ]
    },
    {
        timeout: 1000,
        cmd5: () => {
            document.getElementById("terminal-user-exemple").classList.remove("shy");
        }
    }
];

function animateTerminal(config) {
    let timeout = 0;

    config.forEach(elem => {
        if (elem.cmd) {
            timeout += elem.timeout;
            setTimeout(() => {
                elem.cmd();
            }, timeout);
        } else if (elem.cmd2) {
            timeout += elem.timeout;
            setTimeout(() => {
                elem.cmd2();
            }, timeout);
        } else if (elem.cmd3) {
            timeout += elem.timeout;
            setTimeout(() => {
                elem.cmd3();
            }, timeout);
        } else if (elem.cmd4) {
            timeout += elem.timeout;
            setTimeout(() => {
                elem.cmd4();
            }, timeout);
        } else if (elem.cmd5) {
            timeout += elem.timeout;
            setTimeout(() => {
                elem.cmd5();
            }, timeout);
        }
        else {
            for (let i in elem.data) {
                timeout += elem.timeout;

                setTimeout(() => {
                    document.getElementById(elem.elemId).innerHTML = document.getElementById(elem.elemId).innerHTML + elem.data[i];
                    document.getElementById("terminal-container-test").scrollTo(0, document.getElementById("terminal-container-test").scrollHeight);
                }, timeout);
            }
        }
    });
}

animateTerminal(config);
animateTerminal(config2);


// SOCKETS PART

let HistoryCmd = [""];
let HistoryCount = 0;
let HistoryCounter = 0;
let ClickCount = 0;
let DemoCmd = ["help", "help history", "help cowsay", "help demo", "help clear",
    "history", "history -c",
    "cowsay",
    "demo",
    "clear"]
const termContainer = document.getElementById("terminal-container-exemple");

document.getElementById('terminal-input-exemple').addEventListener('keydown', (e) => {
    if (e.key == "Enter") {
        e.preventDefault();
        if (e.target.innerHTML && e.target.innerHTML.length > 0 && e.target.innerHTML.replace(/[ ]+/i, '').length > 0) {
            e.target.innerHTML = e.target.innerHTML.replace(/^(\s+)?(.*?)(\s+)?$/, "$2");
            if (e.target.innerHTML != HistoryCmd[HistoryCounter - 1]) {
                HistoryCmd[HistoryCounter] = e.target.innerHTML;
                HistoryCounter++;
                HistoryCount++;
            }
            appendTerminal(`anonymous@demoterminal:~ $ ${e.target.innerHTML}`);
            if (DemoCmd.includes(e.target.innerHTML)) {
                if (e.target.innerHTML == "help") {
                    document.getElementById("terminal-content-user-exemple").innerHTML = document.getElementById("terminal-content-user-exemple").innerHTML +
                        "<div>help [-dms] [pattern ...]</div>" +
                        "<div>history [-c] [-d offset] [n] or history -anrw [filename] or history -p arg [arg...]</div>";
                } else if (e.target.innerHTML == "help history") {
                    document.getElementById("terminal-content-user-exemple").innerHTML = document.getElementById("terminal-content-user-exemple").innerHTML +
                        "<div>help [-dms] [pattern ...]</div>" +
                        "<div>history [-c] [-d offset] [n] or history -anrw [filename] or history -p arg [arg...]</div>" +
                        "<div>Display or manipulate the history list.</div>" +
                        "<div>Display the history list with line numbers, prefixing each modified entry with a `*'. An argument of N lists only the last N entries.</div>" +
                        "<div>Options:</div>" +
                        "<div>-c clear the history list by deleting all of the entries</div>" +
                        "<div>-d offset delete the history entry at position OFFSET.</div>" +
                        "<div>-a append history lines from this session to the history file</div>" +
                        "<div>-n read all history lines not already read from the history file and append them to the history list</div>" +
                        "<div>-r read the history file and append the contents to the history list</div>" +
                        "<div>-w write the current history to the history file</div>" +
                        "<div>-p perform history expansion on each ARG and display the result without storing it in the history list</div>" +
                        "<div>-s append the ARGs to the history list as a single entry</div>" +
                        "<div>Arguments:</div>" +
                        "<div>PATTERN If FILENAME is given, it is used as the history file. Otherwise, if HISTFILE has a value, that is used, else ~/.bash_history.</div>" +
                        "<div>If the HISTTIMEFORMAT variable is set and not null, its value is used as a format string for strftime(3) to print the time stamp associated with each displayed history entry. No time stamps are printed otherwise.</div>" +
                        "<div>Exit Status:</div>" +
                        "<div>Returns success unless an invalid option is given or an error occurs.</div>";
                } else if (e.target.innerHTML == "help cowsay") {
                    document.getElementById("terminal-content-user-exemple").innerHTML = document.getElementById("terminal-content-user-exemple").innerHTML +
                        "<div>help [-dms] [pattern ...]</div>" +
                        "<div>cowsay [string]</div>" +
                        "<div>Run cowsay with the inserted string.</div>" +
                        "<div>Let the cow talk ! (even if the cow world didn't exist)</div>";
                } else if (e.target.innerHTML == "help demo") {
                    document.getElementById("terminal-content-user-exemple").innerHTML = document.getElementById("terminal-content-user-exemple").innerHTML +
                        "<div>help [-dms] [pattern ...]</div>" +
                        "<div>Rerun the start demo.</div>";
                } else if (e.target.innerHTML == "help clear") {
                    document.getElementById("terminal-content-user-exemple").innerHTML = document.getElementById("terminal-content-user-exemple").innerHTML +
                        "<div>help [-dms] [pattern ...]</div>" +
                        "<div>clear</div>" +
                        "<div>Clear the display.</div>";
                } else if (e.target.innerHTML == "history") {
                    for (i = 0; i < HistoryCounter; i++) {
                        document.getElementById("terminal-content-user-exemple").innerHTML = document.getElementById("terminal-content-user-exemple").innerHTML +
                            "<div>" + i + " - " + HistoryCmd[i] + "</div>";
                    }
                } else if (e.target.innerHTML == "history -c") {
                    HistoryCmd = [""];
                    HistoryCount = 0;
                    HistoryCounter = 0;
                    document.getElementById("terminal-content-user-exemple").innerHTML = document.getElementById("terminal-content-user-exemple").innerHTML +
                        "<div>History cleared.</div>";
                } else if (e.target.innerHTML == "clear") {
                    document.getElementById("terminal-content-user-exemple").innerHTML = "";
                } else if (e.target.innerHTML == "demo") {
                    document.getElementById("term-test").innerHTML = document.getElementById("term-test").innerHTML +
                        '<div class="terminal container" id="terminal-container-test">' +
                        '<div id="term">' +
                        '<div class="terminal-content" id="terminal-content-user-test">' +
                        '<div id="terminal-content-response-test">' +
                        '<div id="terminal-user-test">user@user:~ $' +
                        '<span class="terminal-input" id="terminal-input-test" contenteditable="false" spellcheck="false"></span>' +
                        "</div>" +
                        "</div>" +
                        "</div>" +
                        "</div>" +
                        "</div>";
                    document.getElementById("terminal-container-exemple").classList.add("shy");
                    document.getElementById("term-test").classList.remove("shy");

                    animateTerminal(config);
                }
            } else {
                document.getElementById("terminal-content-user-exemple").innerHTML = document.getElementById("terminal-content-user-exemple").innerHTML + e.target.innerHTML + " can't be called as an anonymous user. Why don't create an account ? (<a href='/signup'>here</a>)";
            }
            document.getElementById("terminal-container-exemple").scrollTo(0, document.getElementById("terminal-container-exemple").scrollHeight);
            e.target.innerHTML = "";
        }
    }
});

document.getElementById('terminal-input-exemple').addEventListener('keydown', (e) => {
    if (e.key == "ArrowUp") {
        document.getElementById('terminal-input-exemple').addEventListener('keydown', (e) => {
            if (e.key == "Enter") {
                HistoryCount = HistoryCmd.length;
            }
        });
        HistoryCount--;
        if (HistoryCount < 1) {
            HistoryCount = 0;
        }
        document.getElementById('terminal-input-exemple').innerHTML = `${HistoryCmd[HistoryCount]}`;
    } else if (e.key == "ArrowDown") {
        document.getElementById('terminal-input-exemple').addEventListener('keydown', (e) => {
            if (e.key == "Enter") {
                HistoryCount = HistoryCmd.length;
            }
        });
        HistoryCount++;
        if (HistoryCount > HistoryCmd.length - 1) {
            HistoryCount = HistoryCmd.length;
            document.getElementById('terminal-input-exemple').innerHTML = "";
        } else {
            document.getElementById('terminal-input-exemple').innerHTML = `${HistoryCmd[HistoryCount]}`;
        }
    } else if (e.key == "Escape") {
        document.getElementById('terminal-input-exemple').innerHTML = "";
    }
});

function move() {
    console.log("Move");
};

function click(e) {
    ClickCount++;
    if (ClickCount == 1) {
        singleClickTimer = setTimeout((f) => {
            const input = document.getElementById('terminal-input-exemple');

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

function appendTerminal(text) {
    document.getElementById("terminal-content-user-exemple").innerHTML = `${document.getElementById("terminal-content-user-exemple").innerHTML}<div>${text}</div>`;
}