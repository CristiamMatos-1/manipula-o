import { NextResponse, type NextRequest } from "next/server";
import { SESSION_COOKIE, verifySession } from "@/lib/auth/session";

const PUBLIC_ADMIN = ["/admin/login", "/admin/esqueci-senha", "/admin/redefinir-senha"];

/**
 * Primeira barreira (rápida, sem banco): exige cookie de sessão válido em /admin/*
 * e força a troca de senha pendente. A validação definitiva (usuário ativo,
 * papel atual, permissões) acontece nas páginas e Server Actions via guards.ts.
 */
export async function middleware(req: NextRequest) {
  const { pathname } = req.nextUrl;
  if (PUBLIC_ADMIN.some((p) => pathname === p || pathname.startsWith(`${p}/`))) {
    return NextResponse.next();
  }

  const session = await verifySession(req.cookies.get(SESSION_COOKIE)?.value);
  if (!session) {
    const url = req.nextUrl.clone();
    url.pathname = "/admin/login";
    url.search = "";
    return NextResponse.redirect(url);
  }

  if (session.mcp && pathname !== "/admin/alterar-senha") {
    const url = req.nextUrl.clone();
    url.pathname = "/admin/alterar-senha";
    url.search = "";
    return NextResponse.redirect(url);
  }

  const res = NextResponse.next();
  res.headers.set("Cache-Control", "no-store");
  res.headers.set("X-Robots-Tag", "noindex, nofollow");
  return res;
}

export const config = { matcher: ["/admin/:path*"] };
