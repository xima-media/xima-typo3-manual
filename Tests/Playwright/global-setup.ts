import { execSync } from 'child_process';
import * as path from 'path';
import * as fs from 'fs';
import { resetDatabase } from './helpers/db-reset';

export default async function globalSetup(): Promise<void> {
  console.log('Setting up test fixtures...');

  resetDatabase();

  const FIXTURE_PATH = '/var/www/html/Tests/Acceptance/Fixtures';
  const siteConfigSource = '/var/www/html/Tests/Fixtures/sites';
  const siteConfigDest = '/var/www/html/config/sites';
  if (fs.existsSync(siteConfigSource)) {
    execSync(`cp -r "${siteConfigSource}/." "${siteConfigDest}/"`, { stdio: 'inherit' });
  }

  const filesSource = path.join(FIXTURE_PATH, 'Files');
  const filesDest = '/var/www/html/public/fileadmin/Files';
  if (fs.existsSync(filesSource)) {
    execSync(`cp -r "${filesSource}" "${filesDest}"`, { stdio: 'inherit' });
  }

  console.log('Fixture setup complete.');
}
