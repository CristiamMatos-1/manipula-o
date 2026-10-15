export type FormState = {
  error?: string;
  success?: string;
  fieldErrors?: Record<string, string>;
};

export const initialFormState: FormState = {};

import type { ZodError } from "zod";

export function zodFieldErrors(err: ZodError): Record<string, string> {
  const out: Record<string, string> = {};
  for (const issue of err.issues) {
    const key = String(issue.path[0] ?? "_");
    if (!out[key]) out[key] = issue.message;
  }
  return out;
}

export const str = (fd: FormData, key: string) => {
  const v = fd.get(key);
  return typeof v === "string" ? v.trim() : "";
};
