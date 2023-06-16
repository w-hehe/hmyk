
var RENDERER = {
    POINT_INTERVAL: 5,
    FISH_COUNT: 3,
    MAX_INTERVAL_COUNT: 50,
    INIT_HEIGHT_RATE: 0.5,
    THRESHOLD: 50,
    init: function() {
        this.setParameters();
        this.reconstructMethods();
        this.setup();
        this.bindEvent();
        this.render()
    },
    setParameters: function() {
        this.$window = $(window);
        this.$container = $("#jsi-flying-fish-container");
        this.$canvas = $("<canvas />");
        this.context = this.$canvas.appendTo(this.$container).get(0).getContext("2d");
        this.points = [];
        this.fishes = [];
        this.watchIds = []
    },
    createSurfacePoints: function() {
        var a = Math.round(this.width / this.POINT_INTERVAL);
        this.pointInterval = this.width / (a - 1);
        this.points.push(new SURFACE_POINT(this,0));
        for (var b = 1; b < a; b++) {
            var c = new SURFACE_POINT(this,b * this.pointInterval)
                , d = this.points[b - 1];
            c.setPreviousPoint(d);
            d.setNextPoint(c);
            this.points.push(c)
        }
    },
    reconstructMethods: function() {
        this.watchWindowSize = this.watchWindowSize.bind(this);
        this.jdugeToStopResize = this.jdugeToStopResize.bind(this);
        this.startEpicenter = this.startEpicenter.bind(this);
        this.moveEpicenter = this.moveEpicenter.bind(this);
        this.reverseVertical = this.reverseVertical.bind(this);
        this.render = this.render.bind(this)
    },
    setup: function() {
        this.points.length = 0;
        this.fishes.length = 0;
        this.watchIds.length = 0;
        this.intervalCount = this.MAX_INTERVAL_COUNT;
        this.width = this.$container.width();
        this.height = this.$container.height();
        this.fishCount = this.FISH_COUNT * this.width / 500 * this.height / 500;
        this.$canvas.attr({
            width: this.width,
            height: this.height
        });
        this.reverse = false;
        this.fishes.push(new FISH(this));
        this.createSurfacePoints()
    },
    watchWindowSize: function() {
        this.clearTimer();
        this.tmpWidth = this.$window.width();
        this.tmpHeight = this.$window.height();
        this.watchIds.push(setTimeout(this.jdugeToStopResize, this.WATCH_INTERVAL))
    },
    clearTimer: function() {
        while (this.watchIds.length > 0) {
            clearTimeout(this.watchIds.pop())
        }
    },
    jdugeToStopResize: function() {
        var c = this.$window.width()
            , a = this.$window.height()
            , b = (c == this.tmpWidth && a == this.tmpHeight);
        this.tmpWidth = c;
        this.tmpHeight = a;
        if (b) {
            this.setup()
        }
    },
    bindEvent: function() {
        this.$window.on("resize", this.watchWindowSize);
        this.$container.on("mouseenter", this.startEpicenter);
        this.$container.on("mousemove", this.moveEpicenter);
        this.$container.on("click", this.reverseVertical)
    },
    getAxis: function(a) {
        var b = this.$container.offset();
        return {
            x: a.clientX - b.left + this.$window.scrollLeft(),
            y: a.clientY - b.top + this.$window.scrollTop()
        }
    },
    startEpicenter: function(a) {
        this.axis = this.getAxis(a)
    },
    moveEpicenter: function(b) {
        var a = this.getAxis(b);
        if (!this.axis) {
            this.axis = a
        }
        this.generateEpicenter(a.x, a.y, a.y - this.axis.y);
        this.axis = a
    },
    generateEpicenter: function(c, d, b) {
        if (d < this.height / 2 - this.THRESHOLD || d > this.height / 2 + this.THRESHOLD) {
            return
        }
        var a = Math.round(c / this.pointInterval);
        if (a < 0 || a >= this.points.length) {
            return
        }
        this.points[a].interfere(d, b)
    },
    reverseVertical: function() {
        this.reverse = !this.reverse;
        for (var b = 0, a = this.fishes.length; b < a; b++) {
            this.fishes[b].reverseVertical()
        }
    },
    controlStatus: function() {
        for (var b = 0, a = this.points.length; b < a; b++) {
            this.points[b].updateSelf()
        }
        for (var b = 0, a = this.points.length; b < a; b++) {
            this.points[b].updateNeighbors()
        }
        if (this.fishes.length < this.fishCount) {
            if (--this.intervalCount == 0) {
                this.intervalCount = this.MAX_INTERVAL_COUNT;
                this.fishes.push(new FISH(this))
            }
        }
    },
    render: function() {
        requestAnimationFrame(this.render);
        this.controlStatus();
        this.context.clearRect(0, 0, this.width, this.height);
        this.context.fillStyle = "hsl(0, 0%, 95%)";
        for (var b = 0, a = this.fishes.length; b < a; b++) {
            this.fishes[b].render(this.context)
        }
        this.context.save();
        this.context.globalCompositeOperation = "xor";
        this.context.beginPath();
        this.context.moveTo(0, this.reverse ? 0 : this.height);
        for (var b = 0, a = this.points.length; b < a; b++) {
            this.points[b].render(this.context)
        }
        this.context.lineTo(this.width, this.reverse ? 0 : this.height);
        this.context.closePath();
        this.context.fill();
        this.context.restore()
    }
};
var SURFACE_POINT = function(a, b) {
    this.renderer = a;
    this.x = b;
    this.init()
};
SURFACE_POINT.prototype = {
    SPRING_CONSTANT: 0.03,
    SPRING_FRICTION: 0.9,
    WAVE_SPREAD: 0.3,
    ACCELARATION_RATE: 0.01,
    init: function() {
        this.initHeight = this.renderer.height * this.renderer.INIT_HEIGHT_RATE;
        this.height = this.initHeight;
        this.fy = 0;
        this.force = {
            previous: 0,
            next: 0
        }
    },
    setPreviousPoint: function(a) {
        this.previous = a
    },
    setNextPoint: function(a) {
        this.next = a
    },
    interfere: function(b, a) {
        this.fy = this.renderer.height * this.ACCELARATION_RATE * ((this.renderer.height - this.height - b) >= 0 ? -1 : 1) * Math.abs(a)
    },
    updateSelf: function() {
        this.fy += this.SPRING_CONSTANT * (this.initHeight - this.height);
        this.fy *= this.SPRING_FRICTION;
        this.height += this.fy
    },
    updateNeighbors: function() {
        if (this.previous) {
            this.force.previous = this.WAVE_SPREAD * (this.height - this.previous.height)
        }
        if (this.next) {
            this.force.next = this.WAVE_SPREAD * (this.height - this.next.height)
        }
    },
    render: function(a) {
        if (this.previous) {
            this.previous.height += this.force.previous;
            this.previous.fy += this.force.previous
        }
        if (this.next) {
            this.next.height += this.force.next;
            this.next.fy += this.force.next
        }
        a.lineTo(this.x, this.renderer.height - this.height)
    }
};
var FISH = function(a) {
    this.renderer = a;
    this.init()
};
FISH.prototype = {
    GRAVITY: 0.4,
    init: function() {
        this.direction = Math.random() < 0.5;
        this.x = this.direction ? (this.renderer.width + this.renderer.THRESHOLD) : -this.renderer.THRESHOLD;
        this.previousY = this.y;
        this.vx = this.getRandomValue(4, 10) * (this.direction ? -1 : 1);
        if (this.renderer.reverse) {
            this.y = this.getRandomValue(this.renderer.height * 1 / 10, this.renderer.height * 4 / 10);
            this.vy = this.getRandomValue(2, 5);
            this.ay = this.getRandomValue(0.05, 0.2)
        } else {
            this.y = this.getRandomValue(this.renderer.height * 6 / 10, this.renderer.height * 9 / 10);
            this.vy = this.getRandomValue(-5, -2);
            this.ay = this.getRandomValue(-0.2, -0.05)
        }
        this.isOut = false;
        this.theta = 0;
        this.phi = 0
    },
    getRandomValue: function(b, a) {
        return b + (a - b) * Math.random()
    },
    reverseVertical: function() {
        this.isOut = !this.isOut;
        this.ay *= -1
    },
    controlStatus: function(a) {
        this.previousY = this.y;
        this.x += this.vx;
        this.y += this.vy;
        this.vy += this.ay;
        if (this.renderer.reverse) {
            if (this.y > this.renderer.height * this.renderer.INIT_HEIGHT_RATE) {
                this.vy -= this.GRAVITY;
                this.isOut = true
            } else {
                if (this.isOut) {
                    this.ay = this.getRandomValue(0.05, 0.2)
                }
                this.isOut = false
            }
        } else {
            if (this.y < this.renderer.height * this.renderer.INIT_HEIGHT_RATE) {
                this.vy += this.GRAVITY;
                this.isOut = true
            } else {
                if (this.isOut) {
                    this.ay = this.getRandomValue(-0.2, -0.05)
                }
                this.isOut = false
            }
        }
        if (!this.isOut) {
            this.theta += Math.PI / 20;
            this.theta %= Math.PI * 2;
            this.phi += Math.PI / 30;
            this.phi %= Math.PI * 2
        }
        this.renderer.generateEpicenter(this.x + (this.direction ? -1 : 1) * this.renderer.THRESHOLD, this.y, this.y - this.previousY);
        if (this.vx > 0 && this.x > this.renderer.width + this.renderer.THRESHOLD || this.vx < 0 && this.x < -this.renderer.THRESHOLD) {
            this.init()
        }
    },
    render: function(a) {
        a.save();
        a.translate(this.x, this.y);
        a.rotate(Math.PI + Math.atan2(this.vy, this.vx));
        a.scale(1, this.direction ? 1 : -1);
        a.beginPath();
        a.moveTo(-30, 0);
        a.bezierCurveTo(-20, 15, 15, 10, 40, 0);
        a.bezierCurveTo(15, -10, -20, -15, -30, 0);
        a.fill();
        a.save();
        a.translate(40, 0);
        a.scale(0.9 + 0.2 * Math.sin(this.theta), 1);
        a.beginPath();
        a.moveTo(0, 0);
        a.quadraticCurveTo(5, 10, 20, 8);
        a.quadraticCurveTo(12, 5, 10, 0);
        a.quadraticCurveTo(12, -5, 20, -8);
        a.quadraticCurveTo(5, -10, 0, 0);
        a.fill();
        a.restore();
        a.save();
        a.translate(-3, 0);
        a.rotate((Math.PI / 3 + Math.PI / 10 * Math.sin(this.phi)) * (this.renderer.reverse ? -1 : 1));
        a.beginPath();
        if (this.renderer.reverse) {
            a.moveTo(5, 0);
            a.bezierCurveTo(10, 10, 10, 30, 0, 40);
            a.bezierCurveTo(-12, 25, -8, 10, 0, 0)
        } else {
            a.moveTo(-5, 0);
            a.bezierCurveTo(-10, -10, -10, -30, 0, -40);
            a.bezierCurveTo(12, -25, 8, -10, 0, 0)
        }
        a.closePath();
        a.fill();
        a.restore();
        a.restore();
        this.controlStatus(a)
    }
};
$(function() {
    RENDERER.init()
});
