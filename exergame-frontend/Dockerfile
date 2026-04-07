# Stage 1: Install dependencies
FROM node:20-slim as deps

WORKDIR /app

# Install pnpm
RUN npm install -g pnpm

# Install build tools
RUN apt-get update && apt-get install -y build-essential python3

COPY package.json pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile


# Stage 2: Build the application
FROM node:20-slim as builder

ARG NUXT_PUBLIC_API_BASE_URL
ARG NUXT_API_INTERNAL_BASE_URL
ARG NUXT_ENCRYPTION_KEY

ENV NUXT_PUBLIC_API_BASE_URL=$NUXT_PUBLIC_API_BASE_URL
ENV NUXT_API_INTERNAL_BASE_URL=$NUXT_API_INTERNAL_BASE_URL
ENV NUXT_ENCRYPTION_KEY=$NUXT_ENCRYPTION_KEY

WORKDIR /app

# Install pnpm
RUN npm install -g pnpm

COPY --from=deps /app/node_modules ./node_modules
COPY . .

RUN pnpm build


# Stage 3: Run the application
FROM node:20-slim

WORKDIR /app

# Install pnpm
RUN npm install -g pnpm

# Set NODE_ENV to production
ENV NODE_ENV=production

# Copy built app from the builder stage
COPY --from=builder /app/.output ./.output
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./package.json

EXPOSE 3000

CMD ["pnpm", "start"]
