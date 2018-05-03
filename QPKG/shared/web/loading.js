    var s = Snap("#loading");

    var svgSize = 100;
    var RoonCircle = s.circle(svgSize / 2, svgSize / 2, svgSize / 2);

    var maskRect = s.paper.rect(0, 0, svgSize / 2, svgSize);
    maskRect.attr({
        fill: "#fff"
    });
    RoonCircle.attr({
        mask: maskRect
    });

    linespacing = svgSize / 100 * 5;
    centerpoint = svgSize / 2;
    linew = svgSize / 100 * 5;
    line1h = svgSize / 100 * 90;
    line2h = svgSize / 100 * 40;
    line3h = svgSize / 100 * 60;
    line4h = svgSize / 100 * 22;
    var line1 = s.rect(centerpoint + (0 * (linew + linespacing)) + linespacing, centerpoint - (line1h / 2), linew, line1h);
    var line2 = s.rect(centerpoint + (1 * (linew + linespacing)) + linespacing, centerpoint - (line2h / 2), linew, line2h);
    var line3 = s.rect(centerpoint + (2 * (linew + linespacing)) + linespacing, centerpoint - (line3h / 2), linew, line3h);
    var line4 = s.rect(centerpoint + (3 * (linew + linespacing)) + linespacing, centerpoint - (line4h / 2), linew, line4h);

    function cw($c_height) {

        var $arr = new Array();
        $arr['y'] = svgSize / 100 * (centerpoint - ($c_height / 2));
        $arr['height'] = svgSize / 100 * $c_height;
        return $arr;

    }

    function roonAnimate() {


        line1.animate(
            cw(52), 200, function () {
                this.animate(
                    cw(96), 200, function () {
                        this.animate(
                            cw(80), 240, function () {
                                this.animate(
                                    cw(86), 200, function () {
                                        this.animate(
                                            cw(92), 40, function () {
                                                this.animate(
                                                    cw(86), 40, function () {
                                                        this.animate(
                                                            {height: svgSize / 100 * 90, y: svgSize / 100 * 5}, 40
                                                        )
                                                    }
                                                )
                                            }
                                        )
                                    }
                                )
                            }
                        )
                    }
                )
            }
        );

        line2.animate(
            cw(76), 280, function () {
                this.animate(
                    cw(30), 200, function () {
                        this.animate(
                            cw(50), 240, function () {
                                this.animate(
                                    cw(55), 120, function () {
                                        this.animate(
                                            cw(57), 40, function () {
                                                this.animate(
                                                    cw(40), 40, function () {
                                                        this.animate(
                                                            {height: svgSize / 100 * 60, y: svgSize / 100 * 20}, 40
                                                        )
                                                    }
                                                )
                                            })
                                    })
                            })
                    })
            });

        line3.animate(
            cw(63), 120, function () { // 3 frames
                this.animate(
                    cw(30), 240, function () { // 6 frames
                        this.animate(
                            cw(70), 200, function () { //5 frames
                                this.animate(
                                    cw(54), 240, function () { // 6 frames
                                        this.animate(
                                            cw(56), 40, function () { //1 frame
                                                this.animate(
                                                    cw(42), 40, function () { // 1 frame
                                                        this.animate(
                                                            cw(58), 40), function () {
                                                            this.animate(
                                                                {height: svgSize / 100 * 22, y: svgSize / 100 * 56}, 40
                                                            )
                                                        }
                                                    })
                                            })
                                    })
                            })
                    })
            });

        line4.animate(
            cw(14), 200, function () {
                this.animate(
                    cw(26), 200, function () {
                        this.animate(
                            cw(8), 240, function () {
                                this.animate(
                                    cw(20), 200, function () {
                                        this.animate(
                                            cw(62), 40, function () {
                                                this.animate(
                                                    cw(24), 40, function () {
                                                        this.animate(
                                                            {height: svgSize / 100 * 40, y: svgSize / 100 * 30}, 40
                                                        )
                                                    }
                                                )
                                            }
                                        )
                                    }
                                )
                            }
                        )
                    }
                )
            }
        );


    }