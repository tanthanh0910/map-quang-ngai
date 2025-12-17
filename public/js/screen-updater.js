// ===============================
//  screen-updater.js
//  Tái sử dụng cho nhiều màn hình
// ===============================

// ===== Helpers =====
export const $ = (sel) => document.querySelector(sel);
export const $$ = (sel) => document.querySelectorAll(sel);

export const formatVN = (n, d = 0) =>
    Number(n || 0).toLocaleString('vi-VN', {
        minimumFractionDigits: d,
        maximumFractionDigits: d
    });


// ===== TimeTillNowManager =====
export function TimeTillNowManager(selector) {
    const el = typeof selector === "string" ? $(selector) : selector;
    let timer = null, start = null, end = null;

    const parseRange = (str) => {
        const [s, e] = str.split(/[-–—]/).map(t => t.trim());
        if (!s || !e) return null;

        const today = dayjs();
        const startT = dayjs(`${today.format("YYYY-MM-DD")} ${s}`, "YYYY-MM-DD HH:mm");
        let endT = dayjs(`${today.format("YYYY-MM-DD")} ${e}`, "YYYY-MM-DD HH:mm");

        if (endT.isBefore(startT)) endT = endT.add(1, "day");
        return { startT, endT };
    };

    const update = () => {
        const now = dayjs();
        if (!start || !end) return;

        if (now.isBefore(start)) {
            el.textContent = formatVN(0, 1) + "H";
            return;
        }

        const stop = now.isAfter(end);
        let elapsed = stop ? end.diff(start, "minute") : now.diff(start, "minute");

        // Trừ thời gian nghỉ trưa 11:30-12:30
        // Tính số phút nghỉ trưa nằm trong khoảng làm việc
        const lunchStart = dayjs(start.format("YYYY-MM-DD") + " 11:30", "YYYY-MM-DD HH:mm");
        const lunchEnd = dayjs(start.format("YYYY-MM-DD") + " 12:30", "YYYY-MM-DD HH:mm");
        // Nếu ca làm giao với khoảng nghỉ trưa thì trừ đi 60 phút
        if (end.isAfter(lunchStart) && start.isBefore(lunchEnd)) {
            // Tìm khoảng giao nhau
            const overlapStart = start.isAfter(lunchStart) ? start : lunchStart;
            const overlapEnd = end.isBefore(lunchEnd) ? end : lunchEnd;
            const lunchMinutes = overlapEnd.diff(overlapStart, "minute");
            if (lunchMinutes > 0) {
                elapsed -= lunchMinutes;
            }
        }

        el.textContent = formatVN(Math.max(0, elapsed) / 60, 1) + "H";

        if (stop) clear();
    };

    const startTimer = (s, e) => {
        clear();
        start = s; end = e;
        update();
        timer = setInterval(update, 1000);
    };

    const clear = () => {
        if (timer) clearInterval(timer);
        timer = null;
    };

    const init = (workStr, serverVal) => {
        if (!workStr) {
            clear();
            if (serverVal !== undefined) {
                el.textContent = formatVN(serverVal, 1) + "H";
            }
            return;
        }

        const parsed = parseRange(workStr);
        if (!parsed) return;

        const now = dayjs();
        if (now.isAfter(parsed.startT) && now.isBefore(parsed.endT)) {
            startTimer(parsed.startT, parsed.endT);
        } else if (serverVal !== undefined) {
            el.textContent = formatVN(serverVal, 1) + "H";
        }
    };

    return { init, clear, getState: () => ({ timer }) };
}


// ===== UI Updater =====
export const UI = {
    setText(sel, value) {
        const el = $(sel);
        if (el) el.textContent = value ?? "-";
    },

    updateMetrics(d) {
        const m = $$(".metric-value");
        if (!m.length) return;
        m[0].textContent = formatVN(d.target_quantity);
        m[1].textContent = formatVN(d.output_passed ?? d.pass_quantity);
        m[2].textContent = formatVN(d.output_defect ?? d.fail_quantity);
    },

    updateInfo(d, timerManager) {
        const i = $$(".info-value");
        if (!i.length) return;

        i[1].textContent = (d.actual_workers ?? "-") + " CN";

        const timerRunning = timerManager && timerManager.getState().timer;
        if (!timerRunning && d.time_till_now !== undefined) {
            $("#tg-time").textContent = formatVN(d.time_till_now, 1) + "H";
        }

        i[3].textContent = formatVN(d.SAM, 1);
        i[4].textContent = d.nvc;
        i[5].textContent = formatVN(d.hsdm, 1) + "%";
        i[6].textContent = formatVN(d.tllmt, 1) + "%";
    },

    updateChart(containerIndex, plan, real, unit = "", decimals = 1, is3rdChart = false) {
        const box = $$(".chart-container")[containerIndex];
        if (!box) return;

        const title = box.querySelector(".chart-title");
        title.textContent = formatVN(real, decimals) + unit;
        if (is3rdChart) {
            title.className = "chart-title " + (real >= plan ? "red" : "green");
        } else {
            title.className = "chart-title " + (real >= plan ? "green" : "red");
        }


        const bars = box.querySelectorAll(".bar-wrapper");
        const max = Math.max(plan, real);

        // bars[0].querySelector(".bar-target").textContent = formatVN(plan, decimals) + unit;
        bars[0].querySelector(".bar").style.height = (plan / max * 100) + "%";

        // bars[1].querySelector(".bar-value").textContent = formatVN(real, decimals) + unit;
        const realBar = bars[1].querySelector(".bar");
        if (is3rdChart) {
            realBar.className = "bar " + (real >= plan ? "red" : "green");
        } else {
            realBar.className = "bar " + (real >= plan ? "green" : "red");
        }
        realBar.style.height = (real / max * 100) + "%";
    }
};


// ===== Core update handler =====
export function applyScreenUpdate(d, timer) {
    if (!d) return;

    UI.setText(".product-card .product-value", d.style_code || d.styleCode);
    UI.setText(".line-title", d.line_code || d.lineCode);
    console.log("d.line_qcs:", d.line_qcs);
    // QC info
    const qcs = Array.isArray(d.line_qcs)
        ? d.line_qcs.join(", ")
        : (d.line_qcs ?? "-");

    UI.setText("#qc_name", qcs);
    UI.setText("#tt_name", d.line_tt || d.lineTT);

    // Working time
    UI.setText(".time-card .time-range", d.working_time || d.workingTime);
    timer.init(d.working_time || d.workingTime, d.time_till_now);

    // Other UI updates
    UI.updateMetrics(d);
    UI.updateInfo(d, timer);

    // Charts
    UI.updateChart(0, d.SAH_CN_PLAN, d.SAH_CN_REAL, "", 2, false);
    UI.updateChart(1, d.hst_plan, d.hst_real, "%", 1, false);
    UI.updateChart(2, d.ttlmt_plan, d.ttlmt_real, "%", 1, true);
}

// ===== Realtime manager with polling fallback =====
export function startRealtimeWithPolling({ socketUrl, room, apiUrl, pollIntervalMs = 2000, onData = applyScreenUpdate, timerManager }) {
    let socket = null;
    let poller = null;
    let connected = false;
    let pollImmediately = true;

    const startPolling = () => {
        if (poller) return;
        const pollFunc = async () => {
            try {
                const url = apiUrl + (apiUrl.includes('?') ? '&' : '?') + `_=${Date.now()}`;
                const resp = await fetch(url, { cache: 'no-store' });
                if (!resp.ok) return;
                const data = await resp.json();
                onData(data, timerManager);
            } catch (e) {
                console.debug('Polling error:', e);
            }
        };

        // Run immediately then interval
        if (pollImmediately) pollFunc();
        poller = setInterval(pollFunc, pollIntervalMs);
        console.info('[RealtimeWithPolling] Started polling', apiUrl, `every ${pollIntervalMs}ms`);
    };

    const stopPolling = () => {
        if (poller) {
            clearInterval(poller);
            poller = null;
            console.info('[RealtimeWithPolling] Stopped polling');
        }
    };

    const startSocket = () => {
        try {
            if (typeof io === 'undefined') {
                console.warn('[RealtimeWithPolling] socket.io not loaded, using polling fallback');
                startPolling();
                return;
            }

            socket = io(socketUrl);

            const connectTimeout = setTimeout(() => {
                if (!socket || !socket.connected) {
                    console.warn('[RealtimeWithPolling] socket did not connect quickly, fallback to polling');
                    startPolling();
                }
            }, 3000);

            socket.on('connect', () => {
                clearTimeout(connectTimeout);
                connected = true;
                stopPolling();
                if (room) socket.emit('join', room);
                console.info('[RealtimeWithPolling] Socket connected', socket.id, 'joined', room);
            });

            socket.on('disconnect', (reason) => {
                connected = false;
                console.warn('[RealtimeWithPolling] Socket disconnected:', reason);
                startPolling();
            });

            socket.on('connect_error', (err) => {
                connected = false;
                console.warn('[RealtimeWithPolling] Socket connect error', err);
                startPolling();
            });

            // Forward update events to onData
            socket.on('screen.updated', (payload) => {
                // Some servers wrap payload into { data: ... } — normalize
                const data = payload && payload.data ? payload.data : payload;
                onData(data, timerManager);
            });

        } catch (e) {
            console.error('[RealtimeWithPolling] Error creating socket connection', e);
            startPolling();
        }
    };

    // Start initial connections
    startSocket();

    return {
        startSocket: () => startSocket(),
        stop() {
            if (socket) socket.disconnect();
            stopPolling();
            socket = null;
        },
        isConnected: () => !!connected,
        isPolling: () => !!poller,
        getSocket: () => socket
    };
}
