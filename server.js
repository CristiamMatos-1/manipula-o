// Arquivo de inicialização para hospedagens com "Setup Node.js App" (cPanel / Passenger).
// Uso local: NODE_ENV=production node server.js (requer `npm run build` antes).
const { createServer } = require("http");
const next = require("next");

const port = parseInt(process.env.PORT || "3000", 10);
const app = next({ dev: false, hostname: "localhost", port });
const handle = app.getRequestHandler();

app
  .prepare()
  .then(() => {
    createServer((req, res) => handle(req, res)).listen(port, () => {
      console.log(`Drogacare no ar na porta ${port}`);
    });
  })
  .catch((err) => {
    console.error("Falha ao iniciar o Next.js", err);
    process.exit(1);
  });
