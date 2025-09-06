FROM node:20

WORKDIR /app

# Copy package.json and install dependencies
COPY ./react/package*.json ./
RUN npm install

# Copy React project
COPY ./react ./

# Build React app
RUN npm run build

# Expose port
EXPOSE 3000

# Start React backend
CMD ["npm", "start"]
