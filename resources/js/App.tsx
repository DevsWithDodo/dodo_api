import React from 'react';
import { HelmetProvider } from 'react-helmet-async';
import { Route, Routes } from 'react-router-dom';
import Layout from './components/Layout';
import ScrollToTop from './components/ScrollToTop';
import HomePage from './pages/HomePage';
import JoinGroup from './pages/JoinGroup';
import PrivacyPolicy from './pages/PrivacyPolicy';

function App() {
  return (
    <HelmetProvider>
      <ScrollToTop />
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<HomePage />} />
          <Route path="privacy-policy" element={<PrivacyPolicy />} />
        </Route>
        {/* Standalone route outside the main layout */}
        <Route path="/join/:invitationCode" element={<JoinGroup />} />
      </Routes>
    </HelmetProvider>
  );
}

export default App; 