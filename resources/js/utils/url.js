// Baca query string dari URL halaman Inertia (aman untuk SSR, tidak memakai window).

/** Nilai query `name` dari `url` bila termasuk `allowed`, selain itu `fallback`. */
export const queryParam = (url, name, allowed, fallback) => {
  const value = new URL(url, 'http://localhost').searchParams.get(name);
  return allowed.includes(value) ? value : fallback;
};
