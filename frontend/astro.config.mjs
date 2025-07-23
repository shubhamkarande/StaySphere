import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';

export default defineConfig({
  integrations: [tailwind()],
  server: {
    port: 4321,
    host: true
  },
  env: {
    schema: {
      PUBLIC_API_URL: {
        context: 'client',
        access: 'public',
        default: 'http://localhost:8000/api'
      }
    }
  }
});