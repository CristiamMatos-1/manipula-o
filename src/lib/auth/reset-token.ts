import { createHash, randomUUID } from "crypto";
import jwt from "jsonwebtoken";
import { env } from "@/lib/env";

export const RESET_TTL_MINUTES = 30;

export const hashToken = (token: string) => createHash("sha256").update(token).digest("hex");

/** Gera um JWT de uso único. O banco guarda apenas o hash e a expiração. */
export function createResetToken(userId: string) {
  const token = jwt.sign({ sub: userId, jti: randomUUID(), purpose: "pwd-reset" }, env.resetSecret, {
    expiresIn: `${RESET_TTL_MINUTES}m`,
    algorithm: "HS256",
  });
  return {
    token,
    tokenHash: hashToken(token),
    expiresAt: new Date(Date.now() + RESET_TTL_MINUTES * 60 * 1000),
  };
}

export function verifyResetToken(token: string): { userId: string } | null {
  try {
    const payload = jwt.verify(token, env.resetSecret, { algorithms: ["HS256"] }) as jwt.JwtPayload;
    if (payload.purpose !== "pwd-reset" || typeof payload.sub !== "string") return null;
    return { userId: payload.sub };
  } catch {
    return null;
  }
}
