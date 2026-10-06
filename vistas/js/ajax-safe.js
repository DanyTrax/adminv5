/**
 * Ejecuta respuesta AJAX segura (JSON o legacy script) sin eval().
 */
function ejecutarRespuestaAjaxSegura(respuesta, redirectDefault) {
    if (respuesta == null) return;

    var data = respuesta;
    if (typeof respuesta === "string") {
        var trimmed = respuesta.trim();
        if (!trimmed) return;
        try {
            data = JSON.parse(trimmed);
        } catch (e) {
            // Legacy: solo permitir swal embebido si es nuestro patrón conocido (no eval genérico)
            if (/^\s*<script[\s>]/.test(trimmed) && trimmed.indexOf("swal(") !== -1) {
                var match = trimmed.match(/type:\s*"([^"]+)".*?title:\s*"([^"]*)".*?text:\s*"([^"]*)"/s);
                if (match) {
                    swal({
                        type: match[1],
                        title: match[2],
                        text: match[3],
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function (result) {
                        if (result.value && redirectDefault) {
                            window.location = redirectDefault;
                        }
                    });
                    return;
                }
            }
            console.error("Respuesta AJAX no JSON:", trimmed.substring(0, 200));
            swal({ type: "error", title: "Error", text: "Respuesta inválida del servidor", showConfirmButton: true, confirmButtonText: "Cerrar" });
            return;
        }
    }

    if (typeof data !== "object") return;

    var ok = data.success === true || data.status === "ok";
    swal({
        type: ok ? "success" : "error",
        title: data.title || (ok ? "Éxito" : "Error"),
        text: data.message || data.error || data.text || "",
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
    }).then(function (result) {
        var dest = data.redirect || redirectDefault;
        if (result.value && dest) {
            window.location = dest;
        }
    });
}
