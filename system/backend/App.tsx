
import React from 'react';
import { HashRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import DashboardLayout from './components/DashboardLayout';
import LoginPage from './pages/LoginPage';
import OrdersPage from './pages/OrdersPage';
import PartnersPage from './pages/PartnersPage';
import StoresPage from './pages/StoresPage';
import ScootersPage from './pages/ScootersPage';
import FinesPage from './pages/FinesPage';
import BannersPage from './pages/BannersPage';
import AccessoriesPage from './pages/AccessoriesPage';
import MembersPage from './pages/MembersPage';
import AdminsPage from './pages/AdminsPage';

const ProtectedRoute: React.FC<{ children: React.ReactElement }> = ({ children }) => {
  const { user, loading } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600 mx-auto"></div>
          <p className="mt-4 text-gray-500 dark:text-gray-400">載入中...</p>
        </div>
      </div>
    );
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  return children;
};

const App: React.FC = () => {
  return (
    <AuthProvider>
      <HashRouter>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/" element={
            <ProtectedRoute>
              <DashboardLayout />
            </ProtectedRoute>
          }>
            <Route index element={<Navigate to="/orders" replace />} />
            <Route path="orders" element={<OrdersPage />} />
            <Route path="partners" element={<PartnersPage />} />
            <Route path="stores" element={<StoresPage />} />
            <Route path="scooters" element={<ScootersPage />} />
            <Route path="fines" element={<FinesPage />} />
            <Route path="banners" element={<BannersPage />} />
            <Route path="accessories" element={<AccessoriesPage />} />
            <Route path="members" element={<MembersPage />} />
            <Route path="admins" element={<AdminsPage />} />
          </Route>
        </Routes>
      </HashRouter>
    </AuthProvider>
  );
};

export default App;
