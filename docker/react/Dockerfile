FROM node:20

WORKDIR /app

# Copy package.json and install dependencies
COPY ./loyalty-app/package*.json ./
RUN npm install

# Copy React project
COPY ./loyalty-app ./

# Build React app
RUN npm run build

# Expose port
EXPOSE 3000

# Start React backend
CMD ["npm", "start"]
