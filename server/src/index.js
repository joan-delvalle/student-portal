import express from "express";
import cors from "cors";
import swaggerUi from "swagger-ui-express";
import fs from "fs";
import YAML from "yaml";

import studentsRouter from "./routes/students.routes.js";
import announcementsRouter from "./routes/announcements.routes.js";
import subjectsRouter from "./routes/subjects.routes.js";
import scheduleRouter from "./routes/schedule.routes.js";
import gradesRouter from "./routes/grades.routes.js";
import enrollmentRouter from "./routes/enrollment.routes.js";
import financeRouter from "./routes/finance.routes.js";
import libraryRouter from "./routes/library.routes.js";
import clinicRouter from "./routes/clinic.routes.js";

const app = express();

app.use(cors());
app.use(express.json());

// Swagger
const swaggerFile = fs.readFileSync("./openapi.yaml", "utf8");
const swaggerDocument = YAML.parse(swaggerFile);

app.use("/docs", swaggerUi.serve, swaggerUi.setup(swaggerDocument));

// Health
app.get("/api/v1/health", (req, res) => {
  res.json({
    status: "ok"
  });
});

// Student Portal Routes
app.use("/api/v1/students", studentsRouter);
app.use("/api/v1/announcements", announcementsRouter);
app.use("/api/v1/subjects", subjectsRouter);
app.use("/api/v1/schedule", scheduleRouter);
app.use("/api/v1/grades", gradesRouter);
app.use("/api/v1/enrollment", enrollmentRouter);
app.use("/api/v1/finance", financeRouter);
app.use("/api/v1/library", libraryRouter);
app.use("/api/v1/clinic", clinicRouter);

const PORT = 8000;

app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
  console.log(`Swagger UI: http://localhost:${PORT}/docs`);
});