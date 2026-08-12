import React from 'react'

const SmallFooter = () => {
  return (
    <footer className="footer-half sub-bg">
      <div className="container">
        <div className="copyrights text-center mt-0">
          <p>
            Copyright © {new Date().getFullYear()} PixelsSoft. All rights reserved
          </p>
        </div>
      </div>
    </footer>
  )
}

export default SmallFooter
