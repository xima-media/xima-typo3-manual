import { execSync } from 'child_process';
import * as path from 'path';
import * as fs from 'fs';

const DB_HOST = 'db';
const DB_USER = 'db';
const DB_PASS = 'db';
const DB_NAME = 'db';
const FIXTURE_PATH = '/var/www/html/Tests/Acceptance/Fixtures';

function mysql(sql: string): void {
  execSync(`mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASS} ${DB_NAME}`, {
    input: sql,
    stdio: ['pipe', 'inherit', 'inherit'],
  });
}

function mysqlFile(filePath: string): void {
  execSync(`mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASS} ${DB_NAME} < "${filePath}"`, {
    shell: '/bin/bash',
    stdio: 'inherit',
  });
}

export function truncateTable(tableName: string): void {
  mysql(`SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE \`${tableName}\`; SET FOREIGN_KEY_CHECKS = 1;`);
}

export function resetDatabase(): void {
  const tables = fs.readdirSync(FIXTURE_PATH)
    .filter(f => f.endsWith('.sql'))
    .map(f => path.basename(f, '.sql'));

  const truncateStatements = tables.map(t => `TRUNCATE TABLE \`${t}\`;`).join(' ');
  mysql(`SET FOREIGN_KEY_CHECKS = 0; ${truncateStatements} SET FOREIGN_KEY_CHECKS = 1;`);

  for (const table of tables) {
    const file = path.join(FIXTURE_PATH, `${table}.sql`);
    mysqlFile(file);
  }
}
