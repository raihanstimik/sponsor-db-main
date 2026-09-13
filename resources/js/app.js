import './bootstrap';

window.spinningCounter = function (config) {
    return {
        ready: false,
        value: config && config.value != null ? String(config.value) : '',
        spins: (config && config.spins) || 2,
        dur: (config && config.duration) || 900,
        stagger: (config && config.stagger) || 50,
        ease: 'cubic-bezier(0.16, 1, 0.3, 1)',

        init: function () {
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            this.ready = true;
            this.$el.classList.add('is-ready');

            if (this.$refs.fallback) {
                this.$refs.fallback.style.display = 'none';
            }
            if (this.$refs.reel) {
                this.$refs.reel.setAttribute('data-ready', 'true');
                this.$refs.reel.style.display = 'inline-flex';
            }

            this.renderReels(this.value);
        },

        renderReels: function (targetStr) {
            var reel = this.$refs.reel;
            if (!reel) return;

            reel.innerHTML = '';

            var chars = Array.from(String(targetStr));
            var strips = [];

            for (var i = 0; i < chars.length; i++) {
                var ch = chars[i];
                if (ch < '0' || ch > '9') {
                    var sep = document.createElement('span');
                    sep.className = 't-reel-sep';
                    sep.textContent = ch;
                    reel.appendChild(sep);
                    continue;
                }

                var digit = parseInt(ch, 10);
                var col = document.createElement('span');
                col.className = 't-reel-col';

                var strip = document.createElement('span');
                strip.className = 't-reel-strip';

                var totalCells = (this.spins + 1) * 10 + 1;
                for (var k = 0; k < totalCells; k++) {
                    var cell = document.createElement('span');
                    cell.className = 't-reel-digit';
                    cell.textContent = String(k % 10);
                    strip.appendChild(cell);
                }

                col.appendChild(strip);
                reel.appendChild(col);
                strips.push({ strip: strip, digit: digit, colIndex: i });
            }

            var sample = reel.querySelector('.t-reel-digit') || reel.querySelector('.t-reel-sep');
            var cellHeight = sample && sample.getBoundingClientRect().height > 0
                ? sample.getBoundingClientRect().height
                : (parseFloat(window.getComputedStyle(this.$el).fontSize) * 1.25 || 28);

            reel.style.setProperty('--reel-cell', cellHeight + 'px');

            for (var s = 0; s < strips.length; s++) {
                strips[s].strip.style.transition = 'none';
                strips[s].strip.style.transform = 'translateY(0)';
            }

            void reel.offsetWidth;

            for (var a = 0; a < strips.length; a++) {
                var item = strips[a];
                var delay = a * this.stagger;
                var targetY = (this.spins * 10 + item.digit) * cellHeight;
                item.strip.style.transition = 'transform ' + this.dur + 'ms ' + this.ease + ' ' + delay + 'ms';
                item.strip.style.transform = 'translateY(-' + targetY + 'px)';
            }
        }
    };
};

if (window.Alpine) {
    window.Alpine.data('spinningCounter', window.spinningCounter);
} else {
    document.addEventListener('alpine:init', function () {
        if (window.Alpine) {
            window.Alpine.data('spinningCounter', window.spinningCounter);
        }
    });
    document.addEventListener('livewire:init', function () {
        if (window.Alpine) {
            window.Alpine.data('spinningCounter', window.spinningCounter);
        }
    });
}
