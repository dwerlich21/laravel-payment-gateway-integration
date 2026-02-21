import {format, parseISO, isBefore, addDays} from 'date-fns';
import {convertDateText} from "@/composables/functions.js";
import {ptBR} from 'date-fns/locale';

export function formatDate(dateStr) {
    if (!dateStr) return '-';
    try {
        if (typeof dateStr === 'object' && dateStr.date) {
            dateStr = dateStr.date;
        }
        const date = typeof dateStr === 'string' ? parseISO(dateStr) : dateStr;
        return format(date, 'dd/MM/yyyy', {locale: ptBR});
    } catch (error) {
        return convertDateText(dateStr);
    }
}

export function getDueDateClass(dueDate) {
    if (!dueDate) return '';
    try {
        const date = typeof dueDate === 'string' ? parseISO(dueDate) : dueDate;
        const today = new Date();
        const threeDaysFromNow = addDays(today, 3);

        if (isBefore(date, today)) {
            return 'bg-danger text-white'; // Vencido
        } else if (isBefore(date, threeDaysFromNow)) {
            return 'bg-warning text-dark';
        }
        return 'bg-success text-white';
    } catch (error) {
        return '';
    }
}