import React from "react";
import { Outlet } from 'react-router-dom';
import Footer from './Footer';
import Navbar from './Navbar';

const Layout = () => {
  return (
    <div className="flex flex-col flex-grow">
      <Navbar />
      <main className="flex flex-col grow shrink-0">
        <Outlet />
        <Footer />
      </main>
    </div>
  );
};

export default Layout; 