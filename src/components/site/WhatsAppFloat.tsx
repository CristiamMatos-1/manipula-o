import { whatsappLink } from "@/lib/embed";

export function WhatsAppFloat({ number }: { number: string }) {
  const href = whatsappLink(number, "Olá! Gostaria de falar com a Drogacare.");
  if (!href) return null;
  return (
    <a
      href={href}
      target="_blank"
      rel="noopener noreferrer"
      aria-label="Falar com a Drogacare pelo WhatsApp"
      className="fixed bottom-4 right-4 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl transition hover:scale-105 hover:shadow-2xl sm:bottom-6 sm:right-6 sm:h-16 sm:w-16"
    >
      <svg viewBox="0 0 32 32" className="h-8 w-8 sm:h-9 sm:w-9" fill="currentColor" aria-hidden="true">
        <path d="M16.04 3C9.4 3 4 8.4 4 15.03c0 2.12.55 4.18 1.6 6L4 28l7.15-1.87a12.03 12.03 0 0 0 4.89 1.03h.01C22.7 27.16 28 21.76 28 15.13 28 11.9 26.74 8.88 24.46 6.6A11.9 11.9 0 0 0 16.04 3Zm0 21.99h-.01a10 10 0 0 1-5.1-1.4l-.37-.22-3.8 1 1.02-3.7-.24-.38a9.97 9.97 0 0 1-1.53-5.3C6.01 9.5 10.5 5 16.05 5c2.68 0 5.2 1.05 7.1 2.95a9.94 9.94 0 0 1 2.94 7.1c0 5.53-4.5 10.04-10.05 10.04Zm5.5-7.5c-.3-.15-1.78-.88-2.06-.98-.27-.1-.47-.15-.67.15-.2.3-.77.98-.94 1.18-.17.2-.35.22-.65.07-.3-.15-1.27-.47-2.42-1.5a9.07 9.07 0 0 1-1.68-2.08c-.18-.3-.02-.46.13-.61.14-.13.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.68-1.64-.93-2.25-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.5s1.07 2.9 1.22 3.1c.15.2 2.1 3.2 5.08 4.5.71.3 1.26.49 1.7.63.71.22 1.36.19 1.87.12.57-.09 1.78-.73 2.03-1.43.25-.7.25-1.3.17-1.43-.07-.12-.27-.2-.57-.35Z" />
      </svg>
    </a>
  );
}
