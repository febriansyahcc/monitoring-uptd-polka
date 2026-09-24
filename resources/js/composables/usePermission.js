import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Cek izin PBAC di frontend (hanya untuk menampilkan/menyembunyikan UI).
 * Penegakan sebenarnya ada di server: middleware `permission:<slug>` di routes/web.php.
 * Logikanya sama dengan pengecekan izin di model User (PHP): admin selalu lolos, role lain membaca
 * daftar slug dari shared props `auth.permissions`.
 */
export function usePermission() {
  const page = usePage();

  const user = computed(() => page.props.auth?.user ?? null);
  const permissions = computed(() => page.props.auth?.permissions ?? []);

  const can = (slug) => {
    if (!user.value) return false;
    if (user.value.role === 'admin') return true;
    return permissions.value.includes(slug);
  };

  return { can, user, permissions };
}
