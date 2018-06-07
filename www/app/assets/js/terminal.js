const conn = new WebSocket(`ws://${window.location.hostname}${location.port ? ':' + location.port : ''}`);

conn.onopen = (e) => {
    const HistoryCmd = [""];
    let HistoryPosition = 0;
    let HystoryLength = 0;
    const termContainer = document.getElementById("terminal-container");
    let ClickCount = 0;
    let connected = false;

    const pressedKeys = {};

    console.log("Connection established!");

    //Get the data send by the user.
    document.getElementById('terminal-input').addEventListener('keydown', e => {
        pressedKeys[e.key] = true;

        if (e.key === "Enter") {
            e.preventDefault();
            if (e.target.innerHTML && e.target.innerHTML.length > 0 && e.target.innerHTML.replace(/[ ]+/i, '').length > 0) {
                e.target.innerHTML = e.target.innerHTML.replace(/^(\s+)?(.*?)(\s+)?$/, "$2");
                //Load the history of actual user
                if (e.target.innerHTML != HistoryCmd[HystoryLength - 1]) {
                    HistoryCmd[HystoryLength] = e.target.innerHTML;
                    HystoryLength++;
                    HistoryPosition++;
                }

                conn.send(e.target.innerHTML);

                if(document.getElementById('terminal-input').style.visibility !== 'hidden') {
                    appendTerminal(e.target.innerHTML);
                }

                e.target.innerHTML = "";
            }
        }
    });

    document.getElementById('terminal-input').addEventListener('keyup', e => {
        pressedKeys[e.key] = false;
    });

    //All the events for setting the actual position of history, ARROWUP and ARROWDOWN
    document.getElementById('terminal-input').addEventListener('keydown', e => {
        if (e.key == "ArrowUp") {
            e.preventDefault();

            document.getElementById('terminal-input').addEventListener('keydown', e => {
                if (e.key == "Enter") {
                    HistoryPosition = HistoryCmd.length;
                }
            });
            HistoryPosition--;
            if (HistoryPosition < 1) {
                HistoryPosition = 0;
            }
            document.getElementById('terminal-input').innerHTML = `${HistoryCmd[HistoryPosition]}`;
        } else if (e.key == "ArrowDown") {
            document.getElementById('terminal-input').addEventListener('keydown', e => {
                if (e.key == "Enter") {
                    HistoryPosition = HistoryCmd.length;
                }
            });
            HistoryPosition++;
            if (HistoryPosition > HistoryCmd.length - 1) {
                HistoryPosition = HistoryCmd.length;
                document.getElementById('terminal-input').innerHTML = "";
            } else {
                document.getElementById('terminal-input').innerHTML = `${HistoryCmd[HistoryPosition]}`;
            }
        } else if (e.key == "Escape") {
            document.getElementById('terminal-input').innerHTML = "";
        }
    });


    //Function for the click, select text and double click event listener
    function click(e) {
        ClickCount++;
        if (ClickCount == 1) {
            singleClickTimer = setTimeout(f => {
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
                } else if (document.selection) //IE 8 and lower
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

    function move() {
        clearTimeout(singleClickTimer);
        ClickCount = 0;
    };

    //Event listener for the focus on the span
    termContainer.addEventListener("click", click, false);

    termContainer.addEventListener("mousedown", e => {
        termContainer.addEventListener("click", click, false);
        mouseDown = setTimeout(f => {

            termContainer.addEventListener("mousemove", move, false);
            termContainer.removeEventListener("click", click, false);

        }, 200);
        termContainer.addEventListener("mouseup", e => {
            clearTimeout(mouseDown);
            termContainer.removeEventListener("mousemove", move, false);
        });
    });

    //socket response
    conn.onmessage = e => {
        const data = e.data.split(/\|(.+)/);

        if (data[0] == "message") {
            appendTerminal(data[1]);
        } else {
            action(data[1]);
        }

        termContainer.scrollTo(0, termContainer.scrollHeight);
    };

    function action(action) {
        const parameters = action.split(/\|(.*)/);

        console.log(parameters);

        if (action == "clear") {
            document.getElementById("terminal-content-user").innerHTML = null;
        } else if(action == "hide input") {
            document.getElementById('terminal-input').style.visibility = "hidden";
        } else if(action == 'show input') {
            document.getElementById('terminal-input').style.visibility = "";
        } else if (parameters[0] == 'nano') {
            nanoMode(parameters[1] ? parameters[1] : '');
        }
    }

    function appendTerminal(text, line_jump = true) {
        document.getElementById("terminal-content-user").innerHTML += text;
    }

    if (document.querySelector('#account-select option[selected]')) {
        document.querySelector('#account-select option[selected]').removeAttribute("selected");
        document.getElementById("account-select").selectedIndex = [].indexOf.call(document.getElementById("account-select").children, document.querySelector('#account-select option[selected]'));
    }

    function nanoMode(content = "") {
        document.getElementById('nano-content').innerText = content;

        document.getElementById('terminal-content-user').classList.add('d-none');
        document.getElementById('terminal-input').classList.add('d-none');

        document.getElementById('nano').classList.remove('d-none');
        document.getElementById('nano').classList.add('d-flex');

        for (let key in pressedKeys) {
            pressedKeys[key] = false;
        }

        const nano = {
            message: document.getElementById('nano-message').innerHTML
        }

        document.getElementById('nano-content').addEventListener('keydown', e => {
            pressedKeys[e.key] = true;

            if (pressedKeys['Ctrl'] && pressedKeys['x']) {
                console.log('yes');

                nano.message = "yo";
            }
        });

        document.getElementById('nano-content').addEventListener('keyup', e => {
            pressedKeys[e.key] = false;
        });
    }
};