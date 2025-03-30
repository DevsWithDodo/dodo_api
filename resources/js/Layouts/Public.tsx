import Footer from '@/js/Components/Footer';
import Navbar from '@/js/Components/Navbar';
import React from "react";
import { Outlet } from 'react-router-dom';

const Public = () => {
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

export default Public; 