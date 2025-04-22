<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Programación
        </h2>
    </x-slot>

    <!-- ✅ FullCalendar 6.1.8 Global Build -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.global.min.js"></script>
    <script>
        window.Calendar = FullCalendar.Calendar;
        window.dayGridPlugin = FullCalendar.dayGridPlugin;
        window.interactionPlugin = FullCalendar.interactionPlugin;
        window.esLocale = FullCalendar.globalLocales.find(l => l.code === 'es');
    </script>

    <div class="py-6 max-w-4xl mx-auto">
        <script>
            function programacionForm(config) {
                return {
                    startDate: config.fecha_inicio || '',
                    startTime: config.hora_inicio || '08:30',
                    endDate: config.fecha_fin || '',
                    endTime: config.hora_fin || '',
                    csrfToken: config.csrfToken,
                    isLoadingEndDate: false,
                    calculatedEndDateText: '',
                    showEndDateToast: false,

                    calculateEndDate() {
                        this.endDate = ''; this.endTime = '';
                        if (!this.startDate || !config.duracion_horas) return;

                        this.isLoadingEndDate = true;
                        fetch(config.ruta_calculo, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                inicio: this.startDate,
                                hora_inicio: this.startTime,
                                horas: config.duracion_horas
                            })
                        })
                        .then(res => res.ok ? res.json() : res.json().then(err => { throw new Error(err.error) }))
                        .then(data => {
                            this.endDate = data.fecha_fin;
                            this.endTime = data.hora_fin;
                            this.calculatedEndDateText = `${data.fecha_fin} ${data.hora_fin}`;
                            this.showEndDateToast = true;
                        })
                        .catch(error => alert('Error al calcular fecha fin: ' + error.message))
                        .finally(() => {
                            this.isLoadingEndDate = false;
                            setTimeout(() => this.showEndDateToast = false, 4000);
                        });
                    },

                    validateBeforeSubmit(event) {
                        let message = '';
                        if (!document.getElementById('aula_id').value) {
                            message += '⚠️ No se ha seleccionado un aula.\n';
                        }
                        if (!document.getElementById('instructor_id').value) {
                            message += '⚠️ No se ha seleccionado un instructor.\n';
                        }
                        if (message !== '') {
                            this.showToast(message, 'warning');
                        }
                        event.target.submit();
                    },

                    showToast(message, type = 'info') {
                        const toast = document.createElement('div');
                        toast.className = `fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow-lg z-50 text-sm transition-opacity duration-300 ${
                            type === 'warning' ? 'bg-yellow-500 text-black' :
                            type === 'success' ? 'bg-green-600 text-white' :
                            type === 'error' ? 'bg-red-600 text-white' : 'bg-blue-500 text-white'
                        }`;
                        toast.textContent = message;
                        document.body.appendChild(toast);
                        setTimeout(() => toast.classList.add('opacity-0'), 3000);
                        setTimeout(() => toast.remove(), 3500);
                    }
                };
            }
