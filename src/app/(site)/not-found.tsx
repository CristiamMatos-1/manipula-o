import Link from "next/link";

export default function NotFound() {
  return (
    <main className="container py-20 text-center">
      <p className="text-6xl font-extrabold text-brand-blue">404</p>
      <h1 className="mt-4 text-2xl font-extrabold">Página não encontrada</h1>
      <p className="mt-2 text-brand-navy/70">O endereço que você acessou não existe ou não está mais disponível.</p>
      <Link href="/" className="btn-primary mt-6">Voltar ao início</Link>
    </main>
  );
}
