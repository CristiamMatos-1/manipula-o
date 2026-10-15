import nodemailer from "nodemailer";
import { env } from "@/lib/env";

type Mail = { to: string; subject: string; text: string; html: string };

/**
 * Envia e-mail via SMTP_URL. Sem SMTP_URL, o envio é SIMULADO:
 * o conteúdo (incluindo o link) é impresso no console do servidor.
 */
export async function sendMail(mail: Mail): Promise<{ simulated: boolean }> {
  if (!env.smtpUrl) {
    console.log(
      `\n[E-MAIL SIMULADO]\nPara: ${mail.to}\nAssunto: ${mail.subject}\n\n${mail.text}\n`,
    );
    return { simulated: true };
  }
  const transporter = nodemailer.createTransport(env.smtpUrl);
  await transporter.sendMail({ from: env.mailFrom, ...mail });
  return { simulated: false };
}

const escapeHtml = (s: string) =>
  s.replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]!);

export function passwordResetEmail(name: string, link: string, minutes: number): Omit<Mail, "to"> {
  const text = `Olá, ${name}!\n\nRecebemos um pedido para redefinir a senha do painel Drogacare.\nAcesse o link abaixo (válido por ${minutes} minutos e de uso único):\n\n${link}\n\nSe você não fez esse pedido, ignore este e-mail: sua senha continua a mesma.`;
  const html = `<div style="font-family:Arial,sans-serif;color:#061e3b"><h2 style="color:#2775bb">Drogacare</h2><p>Olá, ${escapeHtml(name)}!</p><p>Recebemos um pedido para redefinir a senha do painel. O link é válido por ${minutes} minutos e só pode ser usado uma vez.</p><p><a href="${escapeHtml(link)}" style="background:#2775bb;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;display:inline-block">Redefinir minha senha</a></p><p style="font-size:13px;color:#555">Se você não fez esse pedido, ignore este e-mail.</p></div>`;
  return { subject: "Redefinição de senha - Drogacare", text, html };
}
