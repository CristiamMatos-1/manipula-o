const DEV_SESSION = "dev-only-session-secret-change-me-0123456789abcdef";
const DEV_RESET = "dev-only-reset-secret-change-me-fedcba9876543210";

function secret(name: string, devFallback: string): string {
  const value = process.env[name];
  if (value && value.length >= 32) return value;
  if (process.env.NODE_ENV === "production") {
    throw new Error(`Variável de ambiente ${name} ausente ou curta demais (mínimo 32 caracteres).`);
  }
  return devFallback;
}

export const env = {
  get sessionSecret() {
    return secret("SESSION_SECRET", DEV_SESSION);
  },
  get resetSecret() {
    return secret("RESET_TOKEN_SECRET", DEV_RESET);
  },
  get appUrl() {
    return (process.env.APP_URL || "http://localhost:3000").replace(/\/$/, "");
  },
  get smtpUrl() {
    return process.env.SMTP_URL || "";
  },
  get mailFrom() {
    return process.env.MAIL_FROM || "Drogacare <nao-responda@drogacare.com.br>";
  },
};
