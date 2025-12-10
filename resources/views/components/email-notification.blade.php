@if(session('email_notification'))
    @php
        $notification = session('email_notification');
    @endphp

    <!-- Toast Container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="emailToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
            <div class="toast-header bg-primary text-white">
                <i class="fas fa-envelope me-2"></i>
                <strong class="me-auto">Notificación por Correo</strong>
                <small>Ahora</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <div class="d-flex align-items-start">
                    <div class="spinner-border spinner-border-sm text-primary me-3 mt-1" role="status">
                        <span class="visually-hidden">Enviando...</span>
                    </div>
                    <div>
                        <p class="mb-2"><strong>{{ $notification['title'] ?? 'Correo Electrónico Enviado' }}</strong></p>
                        <p class="mb-1 small">{{ $notification['message'] }}</p>
                        @if(isset($notification['recipient']))
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-user"></i> Para: {{ $notification['recipient'] }}
                            </p>
                        @endif
                        @if(isset($notification['email']))
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-at"></i> {{ $notification['email'] }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar el toast
            var toastEl = document.getElementById('emailToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl, {
                    autohide: false
                });
                toast.show();

                // Simular envío (cambiar spinner por check después de 2 segundos)
                setTimeout(function() {
                    var spinner = toastEl.querySelector('.spinner-border');
                    if (spinner) {
                        spinner.classList.remove('spinner-border');
                        spinner.innerHTML = '<i class="fas fa-check-circle text-success" style="font-size: 1.2rem;"></i>';
                    }

                    // Cambiar header a success
                    var header = toastEl.querySelector('.toast-header');
                    if (header) {
                        header.classList.remove('bg-primary');
                        header.classList.add('bg-success');
                    }

                    // Auto-cerrar después de 5 segundos más
                    setTimeout(function() {
                        toast.hide();
                    }, 5000);
                }, 2000);
            }
        });
    </script>
@endif
