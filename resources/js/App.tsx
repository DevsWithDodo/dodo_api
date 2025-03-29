import React from 'react';
import { HelmetProvider } from 'react-helmet-async';
import { Navigate, Route, Routes } from 'react-router-dom';
import Layout from './Components/Layout';
import ScrollToTop from './Components/ScrollToTop';
import Gate from './Gate';
import Lockscreen from './Layouts/Lockscreen';
import Dashboard from './Pages/Admin/Dashboard';
import Login from './Pages/Admin/Login';
import HomePage from './Pages/Public/Home';
import JoinGroup from './Pages/Public/JoinGroup';
import PrivacyPolicy from './Pages/Public/PrivacyPolicy';
import ApiAdminProvider from './Providers/ApiAdminProvider';

function App() {
    return (
        <HelmetProvider>
            <ScrollToTop />
            <Routes>
                <Route path="/" element={<Layout />}>
                    <Route index element={<HomePage />} />
                    <Route path="privacy-policy" element={<PrivacyPolicy />} />
                </Route>
                <Route path="/join/:invitationCode" element={<JoinGroup />} />
                <Route path="/admin" element={<ApiAdminProvider />}>
                    <Route index element={<Navigate to="/admin/login" />} />
                    <Route path="login" element={<Gate realm="login" />}>
                        <Route element={<Lockscreen />}>
                            <Route index element={<Login />} />
                        </Route>
                    </Route>
                    <Route path="app" element={<Gate realm="admin" />}>
                        <Route index element={<Navigate to="/admin/app/dashboard" />} />
                        <Route path="dashboard" element={<Dashboard />} />
                    </Route>
                </Route>
            </Routes>
        </HelmetProvider>
    );
}

export default App; 