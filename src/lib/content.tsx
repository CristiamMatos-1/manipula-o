import type { ReactNode } from "react";
import { safeUrl } from "./utils";

/**
 * Renderizador de conteúdo simples e SEGURO (sem dangerouslySetInnerHTML).
 * Sintaxe: "# título", "## subtítulo", "- item", **negrito**, [texto](https://link),
 * parágrafos separados por linha em branco.
 */
function inline(text: string, keyBase: string): ReactNode[] {
  const out: ReactNode[] = [];
  const re = /\*\*(.+?)\*\*|\[([^\]]+)\]\(([^)\s]+)\)/g;
  let last = 0;
  let m: RegExpExecArray | null;
  let i = 0;
  while ((m = re.exec(text))) {
    if (m.index > last) out.push(text.slice(last, m.index));
    if (m[1] !== undefined) {
      out.push(<strong key={`${keyBase}-${i++}`}>{m[1]}</strong>);
    } else {
      const href = safeUrl(m[3]);
      out.push(
        href ? (
          <a key={`${keyBase}-${i++}`} href={href} target="_blank" rel="noopener noreferrer nofollow" className="text-brand-blue underline">
            {m[2]}
          </a>
        ) : (
          m[2]
        ),
      );
    }
    last = m.index + m[0].length;
  }
  if (last < text.length) out.push(text.slice(last));
  return out;
}

export function RenderContent({ content }: { content: string }) {
  const blocks = content.replace(/\r\n/g, "\n").split(/\n{2,}/).filter((b) => b.trim());
  return (
    <div className="space-y-4 text-base leading-relaxed text-brand-navy/90 sm:text-lg">
      {blocks.map((block, idx) => {
        const key = `b${idx}`;
        const lines = block.split("\n");
        if (lines.every((l) => /^\s*-\s+/.test(l))) {
          return (
            <ul key={key} className="list-disc space-y-1 pl-6">
              {lines.map((l, j) => (
                <li key={j}>{inline(l.replace(/^\s*-\s+/, ""), `${key}-${j}`)}</li>
              ))}
            </ul>
          );
        }
        if (block.startsWith("## ")) {
          return (
            <h2 key={key} className="pt-2 text-xl font-bold text-brand-blue sm:text-2xl">
              {inline(block.slice(3), key)}
            </h2>
          );
        }
        if (block.startsWith("# ")) {
          return (
            <h2 key={key} className="pt-2 text-2xl font-extrabold text-brand-navy sm:text-3xl">
              {inline(block.slice(2), key)}
            </h2>
          );
        }
        return (
          <p key={key}>
            {lines.map((l, j) => (
              <span key={j}>
                {j > 0 && <br />}
                {inline(l, `${key}-${j}`)}
              </span>
            ))}
          </p>
        );
      })}
    </div>
  );
}
