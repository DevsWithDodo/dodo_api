import React from "react";
import Download from '../components/Download';
import Features from '../components/Features';
import Hero from '../components/Hero';
import Screenshots from '../components/Screenshots';

const HomePage = () => {
  return (
    <div className="overflow-x-hidden">
      <Hero />
      <Features />
      <Screenshots />
      <Download />
    </div>
  );
};

export default HomePage; 