// Shift berjalan dari jam WIT. Aturannya sama dengan app/Support/Shift.php (server):
// pagi 08:00–16:00, sore 16:00–24:00, malam 00:00–08:00.
export const SHIFT_LABELS = { pagi: 'Shift Pagi', sore: 'Shift Sore', malam: 'Shift Malam' };

export const shiftKeyAt = (hour) => (hour >= 8 && hour < 16 ? 'pagi' : hour >= 16 ? 'sore' : 'malam');
