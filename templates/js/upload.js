// Upload progress bar for the admin sample forms (#irevform).
// Progress comes from the browser's own upload byte count (xhr.upload.onprogress),
// so it works no matter how Apache/PHP buffers the request (suexec, fcgid, etc).
(function () {
    var form = document.getElementById("irevform");
    if (!form || !window.XMLHttpRequest || !window.FormData) {
        return; // ancient browser: plain form submit, no progress bar
    }
    form.addEventListener("submit", function (ev) {
        // The server needs the clicked button (executeButton=update / delete), but
        // FormData(form) leaves it out. ev.submitter is that button. If this browser
        // doesn't report one, let the browser do a normal submit (no progress bar).
        var btn = ev.submitter;
        if (!btn) {
            return;
        }
        ev.preventDefault();

        var data = new FormData(form);
        if (btn.name) {
            data.append(btn.name, btn.value);
        }

        var bar = document.getElementById("bar_blank");
        var fill = document.getElementById("bar_color");
        var status = document.getElementById("status");
        var xhr = new XMLHttpRequest();

        bar.style.display = "block";
        fill.style.width = "0%";
        status.textContent = "0%";

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                var pct = Math.floor(e.loaded / e.total * 100);
                fill.style.width = pct + "%";
                // bytes are all sent at 100%; the server still has resizing/transcoding to do
                status.textContent = (pct < 100) ? pct + "%" : "Saving...";
            }
        };
        xhr.onload = function () {
            // show the server's result page, just like a normal form post would have
            document.open();
            document.write(xhr.responseText);
            document.close();
        };
        xhr.onerror = function () {
            status.textContent = "Upload failed. Check your connection and try again.";
        };
        xhr.open("POST", form.getAttribute("action"));
        xhr.send(data);
    });
})();
