# Use build argument to choose dev or prod
ARG NODE_ENV=development
FROM node:20

WORKDIR /app

# Copy package.json and install dependencies
COPY ./loyalty-app/package*.json ./
RUN npm install

# Copy the React app
COPY ./loyalty-app ./

# Expose ports
# Dev: 5173, Prod: 3000
EXPOSE 5173 3000

# Build for production if NODE_ENV=production
RUN if [ "$NODE_ENV" = "production" ]; then npm run build; fi

# Start command
CMD if [ "$NODE_ENV" = "production" ]; \
    then npx serve -s dist -l 3000; \
    else npm run dev -- --host 0.0.0.0; \
    fi
