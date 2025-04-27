eventDidMount: function(info) {
    const viewType = info.view.type;

    if (viewType === 'timeGridWeek') {
        const almuerzoHoras = ['12:00', '13:00'];

        // Crear visual de almuerzo solamente en la hora 12:00-13:00
        const horaEvento = info.event.start.toISOString().substr(11, 5);

        if (almuerzoHoras.includes(horaEvento) && info.event.title === 'Almuerzo') {
            info.el.style.backgroundColor = '#d1d5db'; // Gris suave
            info.el.style.border = 'none';
            info.el.style.color = '#374151'; // Texto gris oscuro
            info.el.innerHTML = '<div style="text-align:center;font-weight:bold;">Almuerzo</div>';
        }
    }
}
