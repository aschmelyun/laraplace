<template>
    <div class="wrapper">
        <canvas width="100" height="100" ref="place" @click="canvasClicked"></canvas>
        <div id="overlay" v-if="activePixel" :style="overlayStyle"></div>
        <div id="colorPicker" v-if="activePixel">
            <p>Last changed by: <strong>{{ activePixelEmail }}</strong></p>
            <nav>
                <button
                    v-for="color in Object.keys(colorOptions)"
                    :key="color"
                    :class="color"
                    @click="changePixel(color)"
                    :style="{background: 'rgb(' + colorOptions[color] + ')'}"
                >
                </button>
            </nav>
        </div>
    </div>
</template>
<script setup>
    import {ref, onMounted, reactive, computed} from 'vue';
    import Echo from 'laravel-echo';
    import Pusher from 'pusher-js';

    const map = ref([]);
    const place = ref(null);
    const activePixel = ref(null);
    const overlayStyle = reactive({top: '0px', left: '0px'});
    const colorOptions = {
        red: '231, 76, 60',
        orange: '230, 126, 34',
        yellow: '241, 196, 15',
        green: '46, 204, 113',
        blue: '52, 152, 219',
        purple: '155, 89, 182',
        white: '236, 240, 241',
        black: '44, 62, 80'
    }

    onMounted(() => {
        fetch('/map', {
            headers: {
                'Content-Type': 'application/json',
                'Accepts': 'application/json',
            }
        })
        .then((response) => {
            response.json().then((data) => {
                map.value = data;

                // draw the pixels on the canvas
                let ctx = place.value.getContext('2d');
                data.forEach((column, x) => {
                    column.forEach((pixel, y) => {
                        let [colorIndex, email] = pixel.split(':');

                        ctx.fillStyle = 'rgb(' + colorOptions[Object.keys(colorOptions)[colorIndex]] + ')';
                        ctx.fillRect(x, y, 1, 1);
                    });
                });
            });
        });

        // get echo configured and listening
        let echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            wsHost: window.location.hostname,
            wsPort: 6001,
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss']
        });

        echo.channel('changes')
            .listen('ColorChanged', (e) => {
                let ctx = place.value.getContext('2d');
                let [colorIndex, email] = e.change.color.split(':');

                ctx.fillStyle = 'rgb(' + colorOptions[Object.keys(colorOptions)[colorIndex]] + ')';

                let [x, y] = e.change.key.split(':');
                ctx.fillRect(x, y, 1, 1);

                map.value[x][y] = `${colorIndex}:${email}`;
            });
    });

    const canvasClicked = (e) => {
        let clickedX = Math.floor(e.pageX / 16);
        let clickedY = Math.floor(e.pageY / 16);

        overlayStyle.top = (clickedY * 16) + 'px';
        overlayStyle.left = (clickedX * 16) + 'px';

        activePixel.value = `${clickedX}:${clickedY}`;
    };

    const changePixel = (color) => {
        let ctx = place.value.getContext('2d');
        ctx.fillStyle = 'rgb(' + colorOptions[color] + ')';

        let [x, y] = activePixel.value.split(':');
        ctx.fillRect(x, y, 1, 1);

        fetch('/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accepts': 'application/json'
            },
            body: JSON.stringify({
                key: activePixel.value,
                color: Object.keys(colorOptions).indexOf(color)
            })
        });
    };

    const activePixelEmail = computed(() => {
        if (!activePixel.value) {
            return '';
        }

        let [x, y] = activePixel.value.split(':');
        return map.value[x][y].split(':')[1];
    });
</script>
<style>
    body {
        margin: 0;
    }
    canvas {
        image-rendering: pixelated;
        transform-origin: top left;
        transform: scale(16);
    }
    #overlay {
        position: absolute;
        z-index: 99;
        width: 14px;
        height: 14px;
        border: 1px solid #000;
        box-shadow: 0 0 8px 4px #fff;
    }
    #colorPicker {
        background: #fff;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 1rem;
        text-align: center;
    }
    #colorPicker p {
        margin: 0 0 1rem;
        font-family: Arial, Helvetica, sans-serif;
    }
    #colorPicker button {
        width: 4rem;
        height: 2rem;
        border: 2px solid #000;
        border-radius: 0.5rem;
        margin: 0 0.25rem;
    }
    #colorPicker button:hover {
        opacity: 0.75;
        cursor: pointer;
    }
</style>