$(document).on('click', '.audio-controls .play-audio', function (e) {
    e.preventDefault();

    const controls = $(this).closest('.audio-controls');
    const mode = controls.data('mode');  // "definitions" | "translations"

    const audio = controls.find('audio')[0];
    const source = controls.find('source')[0];
    const select = controls.find('.dialect-select');
    const error = controls.find('.audio-error');

    const audioUrl = select.length ? select.val() : source.src;

    if (!audioUrl) {
        error.text("No audio available.").show();
        return;
    }

    const proxiedUrl = '/audio-proxy/' + encodeURIComponent(audioUrl.split('/').pop());

    // Reset
    error.hide();

    audio.onerror = () => {
        error.text("Audio unavailable.").show();
    };

    audio.onstalled = () => {
        console.warn("Audio stalled");
    };

    if (audio.src !== proxiedUrl) {
        audio.src = proxiedUrl;
        audio.load();

        // Wait until audio is ready before playing
        audio.oncanplaythrough = () => {
            audio.play()
                .catch(err => {
                    console.error("Audio play failed (" + mode + "):", err);
                    error.text("Audio unavailable.").show();
                });
        };
    } else {
        // If already loaded, play immediately
        audio.play()
            .catch(err => {
                console.error("Audio play failed (" + mode + "):", err);
                error.text("Audio unavailable.").show();
            });
    }
});
