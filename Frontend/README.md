# QuickReceipt Frontend

A responsive React TypeScript web application for receipt management, built with modern tools and mobile-first design principles.

## 🚀 Features

- **Responsive Design**: Mobile-first approach with desktop admin features
- **TypeScript**: Full type safety throughout the application
- **Modern React**: Built with React 19 and latest hooks
- **Tailwind CSS**: Utility-first CSS framework for rapid UI development
- **React Router**: Client-side routing with protected routes
- **React Query**: Powerful data fetching and caching
- **Context API**: State management for authentication and app state
- **Axios**: HTTP client with interceptors for API communication

## 📱 Responsive Design

The application is designed with a mobile-first approach:

- **Mobile (< 768px)**: Optimized for mobile devices with hamburger menu navigation
- **Tablet (768px - 1024px)**: Enhanced layout with more space
- **Desktop (> 1024px)**: Full admin interface with sidebar for organization management

### Admin Features (Desktop Only)
- Team creation and management
- User invitation system
- Organization settings
- Advanced analytics and reporting

## 🛠️ Tech Stack

- **React 19** - UI library
- **TypeScript** - Type safety
- **Vite** - Build tool and dev server
- **Tailwind CSS** - Styling
- **React Router DOM** - Routing
- **React Query** - Data fetching
- **Axios** - HTTP client
- **Lucide React** - Icons
- **ESLint** - Code linting

## 📁 Project Structure

```
src/
├── components/          # Reusable UI components
│   ├── ui/             # Basic UI components (Button, Input, Card)
│   ├── layout/         # Layout components (ResponsiveLayout)
│   ├── mobile/         # Mobile-specific components
│   ├── admin/          # Admin-specific components
│   └── forms/          # Form components
├── pages/              # Page components
│   ├── auth/           # Authentication pages
│   ├── dashboard/      # Dashboard pages
│   ├── admin/          # Admin pages
│   └── mobile/         # Mobile-specific pages
├── hooks/              # Custom React hooks
├── services/           # API services and external integrations
├── contexts/           # React Context providers
├── types/              # TypeScript type definitions
├── utils/              # Utility functions
└── constants/          # Application constants
```

## 🚀 Getting Started

### Prerequisites

- Node.js 18+ 
- npm or yarn

### Installation

1. Install dependencies:
```bash
npm install
```

2. Create environment file:
```bash
cp .env.example .env
```

3. Update environment variables in `.env`:
```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=QuickReceipt
VITE_DEBUG=true
```

4. Start development server:
```bash
npm run dev
```

The application will be available at `http://localhost:5173`

### Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run preview` - Preview production build
- `npm run lint` - Run ESLint
- `npm run lint:fix` - Fix ESLint errors
- `npm run type-check` - Run TypeScript type checking
- `npm run clean` - Clean build artifacts

## 🎨 Design System

The application uses a consistent design system with:

- **Colors**: Primary blue theme with semantic color tokens
- **Typography**: System font stack with consistent sizing
- **Spacing**: Tailwind's spacing scale
- **Components**: Reusable UI components with consistent styling
- **Responsive**: Mobile-first breakpoints

## 🔐 Authentication

The app includes a complete authentication system:

- Login/Register forms
- Protected routes
- Token-based authentication
- Automatic token refresh
- Password reset functionality
- Email verification

## 📱 Mobile Features

- Hamburger menu navigation
- Touch-friendly interface
- Optimized for small screens
- Swipe gestures support
- Mobile-specific components

## 🖥️ Desktop Features

- Admin sidebar navigation
- Team management interface
- User invitation system
- Advanced dashboard views
- Multi-column layouts

## 🔧 Development

### Code Style

- ESLint configuration for consistent code style
- TypeScript strict mode enabled
- Prettier integration (recommended)
- Component-based architecture

### State Management

- React Context for global state (auth, theme)
- React Query for server state
- Local state with useState/useReducer

### API Integration

- Axios for HTTP requests
- Automatic token handling
- Request/response interceptors
- Error handling

## 🚀 Deployment

The application can be deployed to any static hosting service:

1. Build the application:
```bash
npm run build
```

2. Deploy the `dist` folder to your hosting service

### Environment Variables

Make sure to set the following environment variables in production:

- `VITE_API_BASE_URL` - Backend API URL
- `VITE_APP_NAME` - Application name
- `VITE_DEBUG` - Debug mode (set to false in production)

## 🤝 Contributing

1. Follow the existing code style
2. Write TypeScript types for all new features
3. Test on both mobile and desktop
4. Update documentation as needed

## 📄 License

This project is part of the QuickReceipt application suite.