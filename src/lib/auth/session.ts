import { SignJWT, jwtVerify } from "jose";
import { cookies } from "next/headers";
import { env } from "@/lib/env";
import { isRole, type Role } from "./rbac";

export const SESSION_COOKIE = "dc_session";
const SESSION_TTL_SECONDS = 60 * 60 * 8; // 8 horas

export type SessionPayload = {
  uid: string;
  role: Role;
  mcp: boolean; // mustChangePassword (usado pelo middleware)
};

const key = () => new TextEncoder().encode(env.sessionSecret);

export async function signSession(payload: SessionPayload): Promise<string> {
  return new SignJWT({ ...payload })
    .setProtectedHeader({ alg: "HS256" })
    .setIssuedAt()
    .setExpirationTime(`${SESSION_TTL_SECONDS}s`)
    .sign(key());
}

export async function verifySession(token: string | undefined): Promise<SessionPayload | null> {
  if (!token) return null;
  try {
    const { payload } = await jwtVerify(token, key(), { algorithms: ["HS256"] });
    if (typeof payload.uid !== "string" || !isRole(payload.role)) return null;
    return { uid: payload.uid, role: payload.role, mcp: payload.mcp === true };
  } catch {
    return null;
  }
}

export async function createSession(payload: SessionPayload) {
  const token = await signSession(payload);
  cookies().set(SESSION_COOKIE, token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax",
    path: "/",
    maxAge: SESSION_TTL_SECONDS,
  });
}

export function destroySession() {
  cookies().set(SESSION_COOKIE, "", { httpOnly: true, path: "/", maxAge: 0 });
}
